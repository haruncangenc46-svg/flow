from flask import Flask
from flask_migrate import Migrate

from .extensions import db, login_manager
from .models import User


def create_app(config_object=None):
    app = Flask(__name__)
    app.config.from_mapping(
        SECRET_KEY="change-this-secret-key",
        SQLALCHEMY_DATABASE_URI="sqlite:///flow.db",
        SQLALCHEMY_TRACK_MODIFICATIONS=False,
    )

    if config_object:
        app.config.from_object(config_object)

    register_extensions(app)
    register_blueprints(app)
    configure_login()

    return app


def register_extensions(app: Flask) -> None:
    db.init_app(app)
    login_manager.init_app(app)
    Migrate(app, db)


def register_blueprints(app: Flask) -> None:
    from .auth import auth_bp
    from .inventory import inventory_bp
    from .accounting import accounting_bp

    app.register_blueprint(auth_bp)
    app.register_blueprint(inventory_bp)
    app.register_blueprint(accounting_bp)


def configure_login() -> None:
    @login_manager.user_loader
    def load_user(user_id: str):
        return User.query.get(int(user_id))

    login_manager.login_view = "auth.login"
    login_manager.login_message_category = "warning"
