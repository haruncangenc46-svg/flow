from decimal import Decimal

from flask import Blueprint, flash, redirect, render_template, request, url_for
from flask_login import login_required

from .extensions import db
from .models import Product, StockTransaction
from .utils import role_required

inventory_bp = Blueprint("inventory", __name__, url_prefix="/inventory")


@inventory_bp.route("/dashboard")
@login_required
def dashboard():
    products = Product.query.order_by(Product.name).all()
    total_stock_value = sum((p.quantity or 0) * (p.unit_price or 0) for p in products)
    recent_transactions = (
        StockTransaction.query.order_by(StockTransaction.created_at.desc()).limit(10).all()
    )
    return render_template(
        "dashboard.html",
        products=products,
        total_stock_value=total_stock_value,
        recent_transactions=recent_transactions,
    )


@inventory_bp.route("/products")
@login_required
def product_list():
    products = Product.query.order_by(Product.name).all()
    return render_template("products.html", products=products)


@inventory_bp.route("/products/create", methods=["GET", "POST"])
@login_required
@role_required(["admin", "manager"])
def create_product():
    if request.method == "POST":
        name = request.form.get("name", "").strip()
        sku = request.form.get("sku", "").strip()
        quantity = int(request.form.get("quantity", 0))
        unit_price = Decimal(request.form.get("unit_price", "0") or "0")

        if not name or not sku:
            flash("İsim ve stok kodu gereklidir", "warning")
        elif Product.query.filter_by(sku=sku).first():
            flash("Bu stok kodu kullanılmaktadır", "danger")
        else:
            product = Product(name=name, sku=sku, quantity=quantity, unit_price=unit_price)
            db.session.add(product)
            db.session.commit()
            flash("Ürün oluşturuldu", "success")
            return redirect(url_for("inventory.product_list"))

    return render_template("product_form.html")


@inventory_bp.route("/products/<int:product_id>/edit", methods=["GET", "POST"])
@login_required
@role_required(["admin", "manager"])
def edit_product(product_id: int):
    product = Product.query.get_or_404(product_id)
    if request.method == "POST":
        product.name = request.form.get("name", product.name)
        product.sku = request.form.get("sku", product.sku)
        product.quantity = int(request.form.get("quantity", product.quantity))
        product.unit_price = Decimal(request.form.get("unit_price", product.unit_price) or "0")
        db.session.commit()
        flash("Ürün güncellendi", "success")
        return redirect(url_for("inventory.product_list"))

    return render_template("product_form.html", product=product)


@inventory_bp.route("/products/<int:product_id>/transactions/new", methods=["GET", "POST"])
@login_required
@role_required(["admin", "manager", "staff"])
def create_transaction(product_id: int):
    product = Product.query.get_or_404(product_id)
    if request.method == "POST":
        transaction_type = request.form.get("transaction_type")
        quantity = int(request.form.get("quantity", 0))
        unit_price = Decimal(request.form.get("unit_price", product.unit_price) or "0")
        note = request.form.get("note", "")

        if transaction_type not in {"purchase", "sale"}:
            flash("İşlem türü geçersiz", "danger")
        elif quantity <= 0:
            flash("Miktar sıfırdan büyük olmalıdır", "danger")
        elif transaction_type == "sale" and quantity > product.quantity:
            flash("Yeterli stok yok", "danger")
        else:
            total_amount = unit_price * quantity
            transaction = StockTransaction(
                product=product,
                quantity=quantity,
                transaction_type=transaction_type,
                unit_price=unit_price,
                total_amount=total_amount,
                note=note,
            )
            if transaction_type == "purchase":
                product.quantity += quantity
            else:
                product.quantity -= quantity
            product.unit_price = unit_price
            db.session.add(transaction)
            db.session.commit()
            flash("İşlem kaydedildi", "success")
            return redirect(url_for("inventory.product_list"))

    return render_template("transaction_form.html", product=product)
