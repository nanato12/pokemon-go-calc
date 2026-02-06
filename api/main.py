"""Pokemon GO IV抽出 APIサーバー."""

import logging
from pathlib import Path

import connexion
from a2wsgi import ASGIMiddleware

logging.basicConfig(level=logging.INFO, format="%(message)s")

spec_dir = Path(__file__).resolve().parent / "docs"
if not spec_dir.exists():
    spec_dir = Path(__file__).resolve().parent.parent / "docs"

connexion_app = connexion.FlaskApp(__name__, specification_dir=str(spec_dir))
connexion_app.add_api("openapi.yaml", pythonic_params=True)

# For gunicorn/WSGI servers - wrap ASGI app with WSGI adapter
app = ASGIMiddleware(connexion_app)
