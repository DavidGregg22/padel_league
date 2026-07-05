"""SQLite persistence for padel league data."""

from __future__ import annotations

import sqlite3
from pathlib import Path
from typing import Optional

from padel_league.models import Match, Player, Round, Season


DEFAULT_DB = Path.home() / ".padel_league" / "league.db"


class Storage:
    def __init__(self, db_path: Path | str = DEFAULT_DB) -> None:
        self.db_path = Path(db_path)
        self.db_path.parent.mkdir(parents=True, exist_ok=True)
        self._init_db()

    def _connect(self) -> sqlite3.Connection:
        conn = sqlite3.connect(self.db_path)
        conn.row_factory = sqlite3.Row
        conn.execute("PRAGMA foreign_keys = ON")
        return conn

    def _init_db(self) -> None:
        with self._connect() as conn:
            conn.executescript(
                """
                CREATE TABLE IF NOT EXISTS players (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL UNIQUE
                );

                CREATE TABLE IF NOT EXISTS seasons (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    year INTEGER NOT NULL,
                    UNIQUE(name, year)
                );

                CREATE TABLE IF NOT EXISTS season_players (
                    season_id INTEGER NOT NULL,
                    player_id INTEGER NOT NULL,
                    PRIMARY KEY (season_id, player_id),
                    FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE,
                    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
                );

                CREATE TABLE IF NOT EXISTS rounds (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    season_id INTEGER NOT NULL,
                    round_number INTEGER NOT NULL,
                    name TEXT NOT NULL,
                    UNIQUE(season_id, round_number),
                    FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE
                );

                CREATE TABLE IF NOT EXISTS matches (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    round_id INTEGER NOT NULL,
                    team_a_p1_id INTEGER NOT NULL,
                    team_a_p2_id INTEGER NOT NULL,
                    team_b_p1_id INTEGER NOT NULL,
                    team_b_p2_id INTEGER NOT NULL,
                    score_a INTEGER,
                    score_b INTEGER,
                    played INTEGER NOT NULL DEFAULT 0,
                    FOREIGN KEY (round_id) REFERENCES rounds(id) ON DELETE CASCADE,
                    FOREIGN KEY (team_a_p1_id) REFERENCES players(id),
                    FOREIGN KEY (team_a_p2_id) REFERENCES players(id),
                    FOREIGN KEY (team_b_p1_id) REFERENCES players(id),
                    FOREIGN KEY (team_b_p2_id) REFERENCES players(id)
                );
                """
            )

    # --- Players ---

    def add_player(self, name: str) -> Player:
        name = name.strip()
        if not name:
            raise ValueError("Player name cannot be empty.")
        with self._connect() as conn:
            try:
                cur = conn.execute("INSERT INTO players (name) VALUES (?)", (name,))
            except sqlite3.IntegrityError:
                raise ValueError(f"Player '{name}' already exists.")
            return Player(id=cur.lastrowid, name=name)

    def get_player(self, player_id: int) -> Optional[Player]:
        with self._connect() as conn:
            row = conn.execute("SELECT id, name FROM players WHERE id = ?", (player_id,)).fetchone()
            if row is None:
                return None
            return Player(id=row["id"], name=row["name"])

    def get_player_by_name(self, name: str) -> Optional[Player]:
        with self._connect() as conn:
            row = conn.execute(
                "SELECT id, name FROM players WHERE LOWER(name) = LOWER(?)", (name.strip(),)
            ).fetchone()
            if row is None:
                return None
            return Player(id=row["id"], name=row["name"])

    def list_players(self) -> list[Player]:
        with self._connect() as conn:
            rows = conn.execute("SELECT id, name FROM players ORDER BY name").fetchall()
            return [Player(id=r["id"], name=r["name"]) for r in rows]

    # --- Seasons ---

    def create_season(self, name: str, year: int) -> Season:
        with self._connect() as conn:
            try:
                cur = conn.execute(
                    "INSERT INTO seasons (name, year) VALUES (?, ?)", (name.strip(), year)
                )
            except sqlite3.IntegrityError:
                raise ValueError(f"Season '{name}' ({year}) already exists.")
            return Season(id=cur.lastrowid, name=name.strip(), year=year)

    def get_season(self, season_id: int) -> Optional[Season]:
        with self._connect() as conn:
            row = conn.execute(
                "SELECT id, name, year FROM seasons WHERE id = ?", (season_id,)
            ).fetchone()
            if row is None:
                return None
            return Season(id=row["id"], name=row["name"], year=row["year"])

    def list_seasons(self) -> list[Season]:
        with self._connect() as conn:
            rows = conn.execute(
                "SELECT id, name, year FROM seasons ORDER BY year DESC, name"
            ).fetchall()
            return [Season(id=r["id"], name=r["name"], year=r["year"]) for r in rows]

    def add_player_to_season(self, season_id: int, player_id: int) -> None:
        with self._connect() as conn:
            conn.execute(
                "INSERT OR IGNORE INTO season_players (season_id, player_id) VALUES (?, ?)",
                (season_id, player_id),
            )

    def remove_player_from_season(self, season_id: int, player_id: int) -> None:
        with self._connect() as conn:
            conn.execute(
                "DELETE FROM season_players WHERE season_id = ? AND player_id = ?",
                (season_id, player_id),
            )

    def get_season_players(self, season_id: int) -> list[Player]:
        with self._connect() as conn:
            rows = conn.execute(
                """
                SELECT p.id, p.name
                FROM players p
                JOIN season_players sp ON sp.player_id = p.id
                WHERE sp.season_id = ?
                ORDER BY p.name
                """,
                (season_id,),
            ).fetchall()
            return [Player(id=r["id"], name=r["name"]) for r in rows]

    # --- Rounds ---

    def create_round(self, season_id: int, round_number: int, name: str) -> Round:
        with self._connect() as conn:
            try:
                cur = conn.execute(
                    "INSERT INTO rounds (season_id, round_number, name) VALUES (?, ?, ?)",
                    (season_id, round_number, name),
                )
            except sqlite3.IntegrityError:
                raise ValueError(f"Round {round_number} already exists for this season.")
            return Round(id=cur.lastrowid, season_id=season_id, round_number=round_number, name=name)

    def get_rounds(self, season_id: int) -> list[Round]:
        with self._connect() as conn:
            rows = conn.execute(
                "SELECT id, season_id, round_number, name FROM rounds WHERE season_id = ? ORDER BY round_number",
                (season_id,),
            ).fetchall()
            return [
                Round(id=r["id"], season_id=r["season_id"], round_number=r["round_number"], name=r["name"])
                for r in rows
            ]

    def get_round(self, round_id: int) -> Optional[Round]:
        with self._connect() as conn:
            row = conn.execute(
                "SELECT id, season_id, round_number, name FROM rounds WHERE id = ?", (round_id,)
            ).fetchone()
            if row is None:
                return None
            return Round(
                id=row["id"],
                season_id=row["season_id"],
                round_number=row["round_number"],
                name=row["name"],
            )

    def delete_round(self, round_id: int) -> None:
        with self._connect() as conn:
            conn.execute("DELETE FROM rounds WHERE id = ?", (round_id,))

    # --- Matches ---

    def create_match(
        self,
        round_id: int,
        team_a: tuple[int, int],
        team_b: tuple[int, int],
    ) -> Match:
        with self._connect() as conn:
            cur = conn.execute(
                """
                INSERT INTO matches
                    (round_id, team_a_p1_id, team_a_p2_id, team_b_p1_id, team_b_p2_id)
                VALUES (?, ?, ?, ?, ?)
                """,
                (round_id, team_a[0], team_a[1], team_b[0], team_b[1]),
            )
            return Match(
                id=cur.lastrowid,
                round_id=round_id,
                team_a_p1_id=team_a[0],
                team_a_p2_id=team_a[1],
                team_b_p1_id=team_b[0],
                team_b_p2_id=team_b[1],
            )

    def record_result(self, match_id: int, score_a: int, score_b: int) -> Match:
        if score_a < 0 or score_b < 0:
            raise ValueError("Scores cannot be negative.")
        with self._connect() as conn:
            conn.execute(
                "UPDATE matches SET score_a = ?, score_b = ?, played = 1 WHERE id = ?",
                (score_a, score_b, match_id),
            )
            row = conn.execute("SELECT * FROM matches WHERE id = ?", (match_id,)).fetchone()
            if row is None:
                raise ValueError(f"Match {match_id} not found.")
            return self._row_to_match(row)

    def get_matches_for_round(self, round_id: int) -> list[Match]:
        with self._connect() as conn:
            rows = conn.execute(
                "SELECT * FROM matches WHERE round_id = ? ORDER BY id", (round_id,)
            ).fetchall()
            return [self._row_to_match(r) for r in rows]

    def get_matches_for_season(self, season_id: int) -> list[Match]:
        with self._connect() as conn:
            rows = conn.execute(
                """
                SELECT m.* FROM matches m
                JOIN rounds r ON r.id = m.round_id
                WHERE r.season_id = ?
                ORDER BY r.round_number, m.id
                """,
                (season_id,),
            ).fetchall()
            return [self._row_to_match(r) for r in rows]

    def get_match(self, match_id: int) -> Optional[Match]:
        with self._connect() as conn:
            row = conn.execute("SELECT * FROM matches WHERE id = ?", (match_id,)).fetchone()
            if row is None:
                return None
            return self._row_to_match(row)

    @staticmethod
    def _row_to_match(row: sqlite3.Row) -> Match:
        return Match(
            id=row["id"],
            round_id=row["round_id"],
            team_a_p1_id=row["team_a_p1_id"],
            team_a_p2_id=row["team_a_p2_id"],
            team_b_p1_id=row["team_b_p1_id"],
            team_b_p2_id=row["team_b_p2_id"],
            score_a=row["score_a"],
            score_b=row["score_b"],
            played=bool(row["played"]),
        )
