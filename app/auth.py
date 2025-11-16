from flask import Blueprint, flash, redirect, render_template, request, url_for
from flask_login import current_user, login_required, login_user, logout_user

from .extensions import db
from .models import User
from .utils import role_required

auth_bp = Blueprint("auth", __name__)


@auth_bp.route("/")
@login_required
def home():
    return redirect(url_for("inventory.dashboard"))


@auth_bp.route("/login", methods=["GET", "POST"])
def login():
    if current_user.is_authenticated:
        return redirect(url_for("inventory.dashboard"))

    if request.method == "POST":
        username = request.form.get("username", "").strip()
        password = request.form.get("password", "")
        user = User.query.filter_by(username=username).first()
        if user and user.check_password(password):
            login_user(user)
            flash("Giriş başarılı", "success")
            return redirect(url_for("inventory.dashboard"))
        flash("Kullanıcı adı veya şifre hatalı", "danger")

    return render_template("login.html")


@auth_bp.route("/logout")
@login_required
def logout():
    logout_user()
    flash("Oturum kapatıldı", "info")
    return redirect(url_for("auth.login"))


@auth_bp.route("/users")
@login_required
@role_required(["admin"])
def user_list():
    users = User.query.order_by(User.username).all()
    return render_template("users.html", users=users)


@auth_bp.route("/users/create", methods=["GET", "POST"])
@login_required
@role_required(["admin"])
def create_user():
    if request.method == "POST":
        username = request.form.get("username", "").strip()
        password = request.form.get("password", "")
        role = request.form.get("role", "staff")
        if not username or not password:
            flash("Kullanıcı adı ve şifre gereklidir", "warning")
        elif User.query.filter_by(username=username).first():
            flash("Bu kullanıcı adı kullanılmaktadır", "danger")
        else:
            user = User(username=username, role=role)
            user.set_password(password)
            db.session.add(user)
            db.session.commit()
            flash("Kullanıcı oluşturuldu", "success")
            return redirect(url_for("auth.user_list"))

    return render_template("user_form.html")
