"""IV抽出ユースケース."""

import logging

from src.application.dto.extract_iv_result import ExtractIvResult
from src.application.ports.image_reader import ImageReader
from src.application.ports.iv_extractor import IvExtractor
from src.application.ports.pokemon_name_extractor import (
    PokemonNameExtractor,
)
from src.infrastructure.pokemon_data import find_by_japanese_name

logger = logging.getLogger(__name__)


class ExtractIvUseCase:
    """スクリーンショットからポケモン名と個体値を抽出するユースケース."""

    def __init__(
        self,
        image_reader: ImageReader,
        name_extractor: PokemonNameExtractor,
        iv_extractor: IvExtractor,
    ) -> None:
        """Initialize.

        Args:
            image_reader: 画像読み込みアダプター
            name_extractor: ポケモン名抽出アダプター
            iv_extractor: 個体値抽出アダプター
        """
        self._image_reader = image_reader
        self._name_extractor = name_extractor
        self._iv_extractor = iv_extractor

    def execute(self, image_path: str) -> ExtractIvResult:
        """ユースケースを実行する.

        Args:
            image_path: スクリーンショット画像のパス

        Returns:
            抽出結果DTO
        """
        image = self._image_reader.read(image_path)
        name = self._name_extractor.extract(image)
        iv = self._iv_extractor.extract(image)

        pokemon_name_en: str | None = None
        dex: int | None = None

        if name is not None:
            entry = find_by_japanese_name(name)
            if entry is not None:
                pokemon_name_en = entry.name_en
                dex = entry.dex
            else:
                logger.warning("Pokemon mapping not found: %s", name)

        return ExtractIvResult(
            pokemon_name=name,
            pokemon_name_en=pokemon_name_en,
            dex=dex,
            attack=iv.attack,
            defense=iv.defense,
            stamina=iv.stamina,
        )
