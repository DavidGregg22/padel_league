"""Data models for the padel league."""

from __future__ import annotations

from dataclasses import dataclass
from typing import Optional


@dataclass
class Player:
    id: int
    name: str


@dataclass
class Season:
    id: int
    name: str
    year: int


@dataclass
class Round:
    id: int
    season_id: int
    round_number: int
    name: str


@dataclass
class Match:
    id: int
    round_id: int
    team_a_p1_id: int
    team_a_p2_id: int
    team_b_p1_id: int
    team_b_p2_id: int
    score_a: Optional[int] = None
    score_b: Optional[int] = None
    played: bool = False

    @property
    def is_draw(self) -> bool:
        return self.played and self.score_a == self.score_b

    def team_a_won(self) -> bool:
        return self.played and self.score_a is not None and self.score_b is not None and self.score_a > self.score_b

    def team_b_won(self) -> bool:
        return self.played and self.score_a is not None and self.score_b is not None and self.score_b > self.score_a

    def team_a_player_ids(self) -> tuple[int, int]:
        return (self.team_a_p1_id, self.team_a_p2_id)

    def team_b_player_ids(self) -> tuple[int, int]:
        return (self.team_b_p1_id, self.team_b_p2_id)


@dataclass
class Standing:
    """Premier League-style individual standing (partners rotate each round)."""

    player_id: int
    player_name: str
    played: int = 0
    won: int = 0
    drawn: int = 0
    lost: int = 0
    sets_for: int = 0
    sets_against: int = 0
    points: int = 0

    @property
    def set_difference(self) -> int:
        return self.sets_for - self.sets_against
