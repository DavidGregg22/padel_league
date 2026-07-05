"""League scheduling, scoring rules, and standings calculation."""

from __future__ import annotations

from padel_league.models import Match, Standing
from padel_league.storage import Storage

# Premier League-style points
WIN_POINTS = 3
DRAW_POINTS = 1
LOSS_POINTS = 0


def validate_roster_size(count: int) -> None:
    if count < 4:
        raise ValueError("A season needs at least 4 players.")
    if count % 2 != 0:
        raise ValueError("Player count must be even (padel is doubles).")
    if count % 4 != 0:
        raise ValueError(
            "Player count must be divisible by 4 so every pair can play each round "
            f"(e.g. 4, 8, 12 — got {count})."
        )


def rotate_players(players: list, round_index: int) -> list:
    """Circle method: fix first player, rotate the rest."""
    if len(players) < 2:
        return list(players)
    n = len(players)
    offset = round_index % (n - 1)
    tail = players[1:]
    rotated_tail = tail[offset:] + tail[:offset]
    return [players[0]] + rotated_tail


def pair_players(players: list) -> list[tuple[int, int]]:
    """Split an even list into consecutive pairs."""
    return [(players[i], players[i + 1]) for i in range(0, len(players), 2)]


def pair_teams_into_matches(pairs: list[tuple[int, int]]) -> list[tuple[tuple[int, int], tuple[int, int]]]:
    """Match first-half pairs against second-half pairs."""
    mid = len(pairs) // 2
    return [(pairs[i], pairs[mid + i]) for i in range(mid)]


def generate_round_fixtures(
    player_ids: list[int], round_index: int
) -> list[tuple[tuple[int, int], tuple[int, int]]]:
    """Generate doubles fixtures for one round with rotated partners."""
    validate_roster_size(len(player_ids))
    ordered = rotate_players(player_ids, round_index)
    pairs = pair_players(ordered)
    return pair_teams_into_matches(pairs)


def rounds_needed(player_count: int) -> int:
    """Number of rounds for each player to partner with everyone once."""
    validate_roster_size(player_count)
    return player_count - 1


class LeagueService:
    def __init__(self, storage: Storage) -> None:
        self.storage = storage

    def generate_full_schedule(self, season_id: int, clear_existing: bool = False) -> list[int]:
        """Generate all rounds and fixtures for a season."""
        players = self.storage.get_season_players(season_id)
        player_ids = [p.id for p in players]
        validate_roster_size(len(player_ids))

        existing = self.storage.get_rounds(season_id)
        if existing and not clear_existing:
            raise ValueError(
                f"Season already has {len(existing)} round(s). "
                "Use clear_existing=True to regenerate."
            )

        if clear_existing:
            for rnd in existing:
                self.storage.delete_round(rnd.id)

        num_rounds = rounds_needed(len(player_ids))
        created_round_ids: list[int] = []

        for i in range(num_rounds):
            rnd = self.storage.create_round(
                season_id, round_number=i + 1, name=f"Matchday {i + 1}"
            )
            fixtures = generate_round_fixtures(player_ids, i)
            for team_a, team_b in fixtures:
                self.storage.create_match(rnd.id, team_a, team_b)
            created_round_ids.append(rnd.id)

        return created_round_ids

    def generate_single_round(self, season_id: int) -> int:
        """Add one more matchday to an existing season."""
        players = self.storage.get_season_players(season_id)
        player_ids = [p.id for p in players]
        validate_roster_size(len(player_ids))

        existing = self.storage.get_rounds(season_id)
        round_index = len(existing)
        rnd = self.storage.create_round(
            season_id,
            round_number=round_index + 1,
            name=f"Matchday {round_index + 1}",
        )
        fixtures = generate_round_fixtures(player_ids, round_index)
        for team_a, team_b in fixtures:
            self.storage.create_match(rnd.id, team_a, team_b)
        return rnd.id

    def record_match_result(self, match_id: int, score_a: int, score_b: int) -> Match:
        return self.storage.record_result(match_id, score_a, score_b)

    def compute_standings(self, season_id: int) -> list[Standing]:
        """Individual standings — each player earns points from their team's result."""
        players = self.storage.get_season_players(season_id)
        stats: dict[int, Standing] = {
            p.id: Standing(player_id=p.id, player_name=p.name) for p in players
        }

        matches = self.storage.get_matches_for_season(season_id)
        for match in matches:
            if not match.played or match.score_a is None or match.score_b is None:
                continue

            sa, sb = match.score_a, match.score_b
            team_a_ids = match.team_a_player_ids()
            team_b_ids = match.team_b_player_ids()

            if match.is_draw:
                for pid in team_a_ids + team_b_ids:
                    s = stats[pid]
                    s.played += 1
                    s.drawn += 1
                    s.points += DRAW_POINTS
                    s.sets_for += sa if pid in team_a_ids else sb
                    s.sets_against += sb if pid in team_a_ids else sa
            elif match.team_a_won():
                for pid in team_a_ids:
                    s = stats[pid]
                    s.played += 1
                    s.won += 1
                    s.points += WIN_POINTS
                    s.sets_for += sa
                    s.sets_against += sb
                for pid in team_b_ids:
                    s = stats[pid]
                    s.played += 1
                    s.lost += 1
                    s.points += LOSS_POINTS
                    s.sets_for += sb
                    s.sets_against += sa
            elif match.team_b_won():
                for pid in team_b_ids:
                    s = stats[pid]
                    s.played += 1
                    s.won += 1
                    s.points += WIN_POINTS
                    s.sets_for += sb
                    s.sets_against += sa
                for pid in team_a_ids:
                    s = stats[pid]
                    s.played += 1
                    s.lost += 1
                    s.points += LOSS_POINTS
                    s.sets_for += sa
                    s.sets_against += sb

        return sorted(
            stats.values(),
            key=lambda s: (-s.points, -s.set_difference, -s.sets_for, s.player_name.lower()),
        )

    def season_progress(self, season_id: int) -> tuple[int, int]:
        """Return (played_matches, total_matches)."""
        matches = self.storage.get_matches_for_season(season_id)
        played = sum(1 for m in matches if m.played)
        return played, len(matches)
