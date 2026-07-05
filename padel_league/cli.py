"""Command-line interface for the padel league manager."""

from __future__ import annotations

import argparse
import sys
from pathlib import Path

from padel_league.league import LeagueService, validate_roster_size
from padel_league.storage import DEFAULT_DB, Storage


def _player_label(storage: Storage, player_id: int) -> str:
    player = storage.get_player(player_id)
    return player.name if player else f"#{player_id}"


def _team_label(storage: Storage, p1: int, p2: int) -> str:
    return f"{_player_label(storage, p1)} & {_player_label(storage, p2)}"


def _print_standings(service: LeagueService, season_id: int) -> None:
    season = service.storage.get_season(season_id)
    if season is None:
        print("Season not found.")
        return

    standings = service.compute_standings(season_id)
    played, total = service.season_progress(season_id)

    print(f"\n{'=' * 72}")
    print(f"  {season.name} ({season.year}) — League Table")
    print(f"  Matches played: {played}/{total}")
    print(f"{'=' * 72}")
    print(f"{'Pos':<4} {'Player':<22} {'P':>3} {'W':>3} {'D':>3} {'L':>3} {'SF':>4} {'SA':>4} {'GD':>4} {'Pts':>4}")
    print("-" * 72)

    for pos, s in enumerate(standings, start=1):
        print(
            f"{pos:<4} {s.player_name:<22} {s.played:>3} {s.won:>3} {s.drawn:>3} {s.lost:>3} "
            f"{s.sets_for:>4} {s.sets_against:>4} {s.set_difference:>+4} {s.points:>4}"
        )
    print()


def _print_round_matches(storage: Storage, round_id: int) -> None:
    rnd = storage.get_round(round_id)
    if rnd is None:
        print("Round not found.")
        return

    matches = storage.get_matches_for_round(round_id)
    print(f"\n  {rnd.name} (Round {rnd.round_number})")
    print("  " + "-" * 60)
    for m in matches:
        team_a = _team_label(storage, m.team_a_p1_id, m.team_a_p2_id)
        team_b = _team_label(storage, m.team_b_p1_id, m.team_b_p2_id)
        if m.played:
            score = f"{m.score_a}-{m.score_b}"
        else:
            score = "vs"
        print(f"  [{m.id:>3}] {team_a:<28} {score:^7} {team_b}")


def _select_season(storage: Storage) -> int | None:
    seasons = storage.list_seasons()
    if not seasons:
        print("No seasons yet. Create one first.")
        return None
    print("\nSeasons:")
    for s in seasons:
        players = storage.get_season_players(s.id)
        print(f"  [{s.id}] {s.name} ({s.year}) — {len(players)} players")
    try:
        return int(input("Season ID: ").strip())
    except ValueError:
        print("Invalid ID.")
        return None


def _select_match(storage: Storage, season_id: int) -> int | None:
    rounds = storage.get_rounds(season_id)
    if not rounds:
        print("No rounds scheduled yet.")
        return None

    for rnd in rounds:
        _print_round_matches(storage, rnd.id)

    try:
        return int(input("\nMatch ID to record result: ").strip())
    except ValueError:
        print("Invalid ID.")
        return None


def _parse_names(
    positional: list[str] | None = None,
    names_csv: str | None = None,
    file_path: Path | None = None,
) -> list[str]:
    """Collect player names from CLI args, comma-separated string, or a file."""
    names: list[str] = []

    if positional:
        names.extend(positional)

    if names_csv:
        names.extend(n.strip() for n in names_csv.split(",") if n.strip())

    if file_path:
        if not file_path.is_file():
            raise ValueError(f"File not found: {file_path}")
        for line in file_path.read_text(encoding="utf-8").splitlines():
            name = line.strip()
            if name and not name.startswith("#"):
                names.append(name)

    seen: set[str] = set()
    unique: list[str] = []
    for name in names:
        key = name.lower()
        if key not in seen:
            seen.add(key)
            unique.append(name)

    if not unique:
        raise ValueError("No player names provided.")
    return unique


def cmd_player_add(args: argparse.Namespace, storage: Storage) -> None:
    try:
        names = _parse_names(args.names, args.names_csv, args.file)
    except ValueError as e:
        print(f"Error: {e}")
        return

    added = 0
    skipped = 0
    for name in names:
        try:
            player = storage.add_player(name)
            print(f"Added player: {player.name} (id={player.id})")
            added += 1
        except ValueError as e:
            print(f"Skipped '{name}': {e}")
            skipped += 1

    print(f"\nDone: {added} added, {skipped} skipped.")


def cmd_player_list(_: argparse.Namespace, storage: Storage) -> None:
    players = storage.list_players()
    if not players:
        print("No players registered.")
        return
    for p in players:
        print(f"  [{p.id}] {p.name}")


def cmd_season_create(args: argparse.Namespace, storage: Storage) -> None:
    season = storage.create_season(args.name, args.year)
    print(f"Created season: {season.name} ({season.year}) id={season.id}")


def cmd_season_list(_: argparse.Namespace, storage: Storage) -> None:
    seasons = storage.list_seasons()
    if not seasons:
        print("No seasons.")
        return
    for s in seasons:
        players = storage.get_season_players(s.id)
        print(f"  [{s.id}] {s.name} ({s.year}) — {len(players)} players: ", end="")
        print(", ".join(p.name for p in players) or "(empty)")

def cmd_season_add_players(args: argparse.Namespace, storage: Storage) -> None:
    season = storage.get_season(args.season_id)
    if season is None:
        print(f"Season {args.season_id} not found.")
        return

    if args.all_players:
        players = storage.list_players()
        if not players:
            print("No players registered.")
            return
        names = [p.name for p in players]
    else:
        try:
            names = _parse_names(args.names, args.names_csv, args.file)
        except ValueError as e:
            print(f"Error: {e}")
            return

    added = 0
    skipped = 0
    for name in names:
        player = storage.get_player_by_name(name)
        if player is None:
            print(f"Skipped '{name}': not registered (use player-add first)")
            skipped += 1
            continue
        roster_before = {p.id for p in storage.get_season_players(args.season_id)}
        if player.id in roster_before:
            print(f"Skipped '{player.name}': already on roster")
            skipped += 1
            continue
        storage.add_player_to_season(args.season_id, player.id)
        print(f"Added {player.name} to season {args.season_id}")
        added += 1

    count = len(storage.get_season_players(args.season_id))
    print(f"\nDone: {added} added, {skipped} skipped. Roster now has {count} players.")
    try:
        validate_roster_size(count)
        print(f"Roster valid — full schedule needs {count - 1} matchdays.")
    except ValueError as e:
        print(f"Note: {e}")


def cmd_season_add_player(args: argparse.Namespace, storage: Storage) -> None:
    player = storage.get_player_by_name(args.player) if not args.player_id else storage.get_player(args.player_id)
    if player is None:
        print(f"Player not found: {args.player or args.player_id}")
        return
    storage.add_player_to_season(args.season_id, player.id)
    count = len(storage.get_season_players(args.season_id))
    print(f"Added {player.name} to season {args.season_id} ({count} players total)")
    try:
        validate_roster_size(count)
        print(f"Roster valid — full schedule needs {count - 1} matchdays.")
    except ValueError as e:
        print(f"Note: {e}")


def cmd_season_remove_player(args: argparse.Namespace, storage: Storage) -> None:
    player = storage.get_player(args.player_id)
    if player is None:
        print("Player not found.")
        return
    storage.remove_player_from_season(args.season_id, player.id)
    print(f"Removed {player.name} from season {args.season_id}")


def cmd_schedule_generate(args: argparse.Namespace, service: LeagueService) -> None:
    try:
        round_ids = service.generate_full_schedule(args.season_id, clear_existing=args.force)
    except ValueError as e:
        print(f"Error: {e}")
        return

    players = service.storage.get_season_players(args.season_id)
    rounds = service.storage.get_rounds(args.season_id)
    print(f"Generated {len(rounds)} matchdays, {len(round_ids)} rounds created.")
    print(f"Players: {len(players)} | Matches per round: {len(players) // 4}")

    for rnd in rounds:
        _print_round_matches(service.storage, rnd.id)


def cmd_schedule_round(args: argparse.Namespace, service: LeagueService) -> None:
    try:
        round_id = service.generate_single_round(args.season_id)
    except ValueError as e:
        print(f"Error: {e}")
        return
    _print_round_matches(service.storage, round_id)


def cmd_result_record(args: argparse.Namespace, service: LeagueService) -> None:
    try:
        match = service.record_match_result(args.match_id, args.score_a, args.score_b)
    except ValueError as e:
        print(f"Error: {e}")
        return

    storage = service.storage
    team_a = _team_label(storage, match.team_a_p1_id, match.team_a_p2_id)
    team_b = _team_label(storage, match.team_b_p1_id, match.team_b_p2_id)
    print(f"Recorded: {team_a} {match.score_a}-{match.score_b} {team_b}")


def cmd_standings(args: argparse.Namespace, service: LeagueService) -> None:
    _print_standings(service, args.season_id)


def cmd_fixtures(args: argparse.Namespace, storage: Storage) -> None:
    rounds = storage.get_rounds(args.season_id)
    if not rounds:
        print("No fixtures for this season.")
        return
    for rnd in rounds:
        _print_round_matches(storage, rnd.id)
    print()


def interactive_menu(storage: Storage, service: LeagueService) -> None:
    current_season_id: int | None = None

    while True:
        print("\n" + "=" * 50)
        print("  PADEL LEAGUE MANAGER")
        if current_season_id:
            season = storage.get_season(current_season_id)
            if season:
                n = len(storage.get_season_players(current_season_id))
                print(f"  Active season: {season.name} ({season.year}) — {n} players")
        print("=" * 50)
        print("  1.  List / create players")
        print("  2.  List / create seasons")
        print("  3.  Select active season")
        print("  4.  Manage season roster")
        print("  5.  Generate full schedule")
        print("  6.  Add single matchday")
        print("  7.  View fixtures")
        print("  8.  Record match result")
        print("  9.  View league table")
        print("  0.  Exit")
        print("-" * 50)

        choice = input("Choice: ").strip()

        if choice == "0":
            print("Goodbye!")
            break

        elif choice == "1":
            print("\nPlayers:")
            cmd_player_list(None, storage)
            names = input(
                "\nAdd players — comma-separated names (Enter to skip): "
            ).strip()
            if names:
                try:
                    cmd_player_add(
                        argparse.Namespace(names=names.split(","), names_csv=None, file=None),
                        storage,
                    )
                except ValueError as e:
                    print(e)

        elif choice == "2":
            cmd_season_list(None, storage)
            name = input("\nNew season name (Enter to skip): ").strip()
            if name:
                try:
                    year = int(input("Year: ").strip())
                    season = storage.create_season(name, year)
                    print(f"Created season id={season.id}")
                    current_season_id = season.id
                except (ValueError, TypeError) as e:
                    print(f"Error: {e}")

        elif choice == "3":
            sid = _select_season(storage)
            if sid:
                current_season_id = sid
                print(f"Active season set to {sid}")

        elif choice == "4":
            if not current_season_id:
                print("Select a season first (option 3).")
                continue
            players = storage.get_season_players(current_season_id)
            print(f"\nRoster ({len(players)} players):")
            for p in players:
                print(f"  [{p.id}] {p.name}")
            print("\n  a = add player   b = add multiple   r = remove player")
            action = input("Action: ").strip().lower()
            if action == "a":
                cmd_player_list(None, storage)
                try:
                    pid = int(input("Player ID to add: ").strip())
                    storage.add_player_to_season(current_season_id, pid)
                    count = len(storage.get_season_players(current_season_id))
                    print(f"Roster now has {count} players.")
                    validate_roster_size(count)
                    print("Roster size is valid for scheduling.")
                except (ValueError, TypeError) as e:
                    print(f"Error: {e}")
            elif action == "b":
                names = input(
                    "Player names (comma-separated), or 'all' for every registered player: "
                ).strip()
                if not names:
                    continue
                if names.lower() == "all":
                    cmd_season_add_players(
                        argparse.Namespace(
                            season_id=current_season_id,
                            all_players=True,
                            names=None,
                            names_csv=None,
                            file=None,
                        ),
                        storage,
                    )
                else:
                    cmd_season_add_players(
                        argparse.Namespace(
                            season_id=current_season_id,
                            all_players=False,
                            names=names.split(","),
                            names_csv=None,
                            file=None,
                        ),
                        storage,
                    )
            elif action == "r":
                try:
                    pid = int(input("Player ID to remove: ").strip())
                    storage.remove_player_from_season(current_season_id, pid)
                    print("Player removed.")
                except ValueError:
                    print("Invalid ID.")

        elif choice == "5":
            if not current_season_id:
                print("Select a season first (option 3).")
                continue
            force = input("Clear existing schedule? (y/N): ").strip().lower() == "y"
            cmd_schedule_generate(
                argparse.Namespace(season_id=current_season_id, force=force), service
            )

        elif choice == "6":
            if not current_season_id:
                print("Select a season first (option 3).")
                continue
            cmd_schedule_round(argparse.Namespace(season_id=current_season_id), service)

        elif choice == "7":
            if not current_season_id:
                print("Select a season first (option 3).")
                continue
            cmd_fixtures(argparse.Namespace(season_id=current_season_id), storage)

        elif choice == "8":
            if not current_season_id:
                print("Select a season first (option 3).")
                continue
            match_id = _select_match(storage, current_season_id)
            if match_id:
                try:
                    sa = int(input("Team A sets won: ").strip())
                    sb = int(input("Team B sets won: ").strip())
                    cmd_result_record(
                        argparse.Namespace(match_id=match_id, score_a=sa, score_b=sb), service
                    )
                except ValueError as e:
                    print(f"Error: {e}")

        elif choice == "9":
            if not current_season_id:
                print("Select a season first (option 3).")
                continue
            _print_standings(service, current_season_id)

        else:
            print("Invalid choice.")


def build_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(
        description="Padel league manager — rotating doubles, scores, and standings."
    )
    parser.add_argument(
        "--db",
        type=Path,
        default=DEFAULT_DB,
        help=f"Database path (default: {DEFAULT_DB})",
    )
    sub = parser.add_subparsers(dest="command")

    # Interactive mode
    sub.add_parser("menu", help="Open interactive menu")

    # Players
    p_add = sub.add_parser("player-add", help="Register one or more players")
    p_add.add_argument(
        "names",
        nargs="*",
        help="Player names (multiple allowed)",
    )
    p_add.add_argument(
        "--names",
        dest="names_csv",
        metavar="NAMES",
        help='Comma-separated names, e.g. "Alice,Bob,Carol"',
    )
    p_add.add_argument(
        "--file",
        type=Path,
        metavar="PATH",
        help="Text file with one player name per line",
    )

    sub.add_parser("player-list", help="List all players")

    # Seasons
    s_create = sub.add_parser("season-create", help="Create a season")
    s_create.add_argument("name")
    s_create.add_argument("year", type=int)

    sub.add_parser("season-list", help="List seasons")

    s_add = sub.add_parser("season-add-players", help="Add multiple players to a season roster")
    s_add.add_argument("season_id", type=int)
    s_add.add_argument("names", nargs="*", help="Player names (must already be registered)")
    s_add.add_argument(
        "--names",
        dest="names_csv",
        metavar="NAMES",
        help='Comma-separated names, e.g. "Alice,Bob,Carol"',
    )
    s_add.add_argument(
        "--file",
        type=Path,
        metavar="PATH",
        help="Text file with one player name per line",
    )
    s_add.add_argument(
        "--all",
        dest="all_players",
        action="store_true",
        help="Add every registered player to the season",
    )

    s_add1 = sub.add_parser("season-add-player", help="Add one player to season roster")
    s_add1.add_argument("season_id", type=int)
    s_add1.add_argument("--player", help="Player name")
    s_add1.add_argument("--player-id", type=int, help="Player ID")

    s_rem = sub.add_parser("season-remove-player", help="Remove player from season")
    s_rem.add_argument("season_id", type=int)
    s_rem.add_argument("player_id", type=int)

    # Schedule
    sched = sub.add_parser("schedule-generate", help="Generate full season schedule")
    sched.add_argument("season_id", type=int)
    sched.add_argument("--force", action="store_true", help="Replace existing schedule")

    sched1 = sub.add_parser("schedule-round", help="Add one matchday")
    sched1.add_argument("season_id", type=int)

    # Results & standings
    res = sub.add_parser("result", help="Record a match result")
    res.add_argument("match_id", type=int)
    res.add_argument("score_a", type=int, help="Sets won by team A")
    res.add_argument("score_b", type=int, help="Sets won by team B")

    stand = sub.add_parser("standings", help="Show league table")
    stand.add_argument("season_id", type=int)

    fix = sub.add_parser("fixtures", help="Show all fixtures")
    fix.add_argument("season_id", type=int)

    return parser


def main(argv: list[str] | None = None) -> int:
    parser = build_parser()
    args = parser.parse_args(argv)

    storage = Storage(args.db)
    service = LeagueService(storage)

    if args.command is None or args.command == "menu":
        interactive_menu(storage, service)
        return 0

    handlers = {
        "player-add": lambda: cmd_player_add(args, storage),
        "player-list": lambda: cmd_player_list(args, storage),
        "season-create": lambda: cmd_season_create(args, storage),
        "season-list": lambda: cmd_season_list(args, storage),
        "season-add-players": lambda: cmd_season_add_players(args, storage),
        "season-add-player": lambda: cmd_season_add_player(args, storage),
        "season-remove-player": lambda: cmd_season_remove_player(args, storage),
        "schedule-generate": lambda: cmd_schedule_generate(args, service),
        "schedule-round": lambda: cmd_schedule_round(args, service),
        "result": lambda: cmd_result_record(args, service),
        "standings": lambda: cmd_standings(args, service),
        "fixtures": lambda: cmd_fixtures(args, storage),
    }

    handler = handlers.get(args.command)
    if handler:
        handler()
        return 0

    parser.print_help()
    return 1


if __name__ == "__main__":
    sys.exit(main())
