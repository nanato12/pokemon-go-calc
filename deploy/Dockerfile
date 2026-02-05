FROM python:3.13-slim

RUN apt-get update && apt-get install -y --no-install-recommends \
    tesseract-ocr \
    tesseract-ocr-jpn \
    libgl1 \
    libglib2.0-0 \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY api/requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY api/pokemon_go_calc/ pokemon_go_calc/
COPY api/main.py .

CMD exec gunicorn --bind :${PORT:-8080} --workers 1 --threads 8 main:app
