"""ロギング設定モジュール."""

import logging
import os
from logging import Logger, getLogger
from logging.handlers import TimedRotatingFileHandler

LOG_DIRECTORY = "logs"


def get_file_path_logger(module: str) -> Logger:
    """モジュール名からロガーを取得.

    Args:
        module: モジュール名

    Returns:
        ロガーインスタンス
    """
    return getLogger(module.replace(".", "/"))


def setup_logging(log_filename: str) -> None:
    """ロギングを設定.

    Args:
        log_filename: ログファイル名 (例: "app.log")
    """
    os.makedirs(LOG_DIRECTORY, exist_ok=True)

    logging.basicConfig(
        level=logging.INFO,
        datefmt="%Y/%m/%d %H:%M:%S",
        format="%(asctime)s [%(levelname)s] %(name)s:%(lineno)s %(message)s",
        handlers=[
            TimedRotatingFileHandler(
                os.path.join(LOG_DIRECTORY, log_filename),
                when="midnight",
                backupCount=30,
                interval=1,
                encoding="utf-8",
            ),
            logging.StreamHandler(),
        ],
    )
