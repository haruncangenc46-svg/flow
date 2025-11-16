from decimal import Decimal

from flask import Blueprint, flash, redirect, render_template, request, url_for
from flask_login import login_required

from .extensions import db
from .models import LedgerEntry
from .utils import role_required

accounting_bp = Blueprint("accounting", __name__, url_prefix="/accounting")


@accounting_bp.route("/ledger")
@login_required
def ledger():
    entries = LedgerEntry.query.order_by(LedgerEntry.created_at.desc()).all()
    totals = {
        "income": sum(e.amount for e in entries if e.entry_type == "income"),
        "expense": sum(e.amount for e in entries if e.entry_type == "expense"),
    }
    totals["balance"] = totals["income"] - totals["expense"]
    return render_template("ledger.html", entries=entries, totals=totals)


@accounting_bp.route("/ledger/create", methods=["GET", "POST"])
@login_required
@role_required(["admin", "manager"])
def create_entry():
    if request.method == "POST":
        entry_type = request.form.get("entry_type")
        description = request.form.get("description", "").strip()
        amount = Decimal(request.form.get("amount", "0") or "0")

        if entry_type not in {"income", "expense"}:
            flash("Kayıt türü geçersiz", "danger")
        elif not description or amount <= 0:
            flash("Açıklama ve tutar zorunludur", "warning")
        else:
            entry = LedgerEntry(entry_type=entry_type, description=description, amount=amount)
            db.session.add(entry)
            db.session.commit()
            flash("Muhasebe kaydı oluşturuldu", "success")
            return redirect(url_for("accounting.ledger"))

    return render_template("ledger_form.html")
