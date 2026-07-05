# Padel League Manager

A Python app for running a padel league with **rotating doubles partners**, **match scores**, and **Premier League-style points** — with a different roster size each season.

## How it works

- **Seasons** — create a new season each year (or whenever you want) with its own player list
- **Rotating partners** — each matchday, players are paired differently so everyone partners with different people over the season
- **Doubles matches** — 2v2 padel; teams of two play against each other
- **Scoring** — record sets won (e.g. 2-1)
- **League table** — individual standings using football-style points:
  - Win = **3 pts**, Draw = **1 pt**, Loss = **0 pts**
  - Tie-breakers: set difference, then sets for

### Roster rules

For scheduling to work cleanly, each season needs:

- **At least 4 players**
- **Even number** of players (doubles)
- **Divisible by 4** (e.g. 4, 8, 12) so every pair can play each round with no one sitting out

A full season generates **N − 1 matchdays** where N is the player count, so everyone gets a chance to partner with different players.

## Quick start

```bash
cd /home/david/Documents/Padel

# Interactive menu (easiest)
python main.py

# Or use CLI commands directly
python main.py player-add Alice Bob Carol Dave Eve Frank Grace Henry

python main.py season-create "Summer League" 2026
python main.py season-add-players 1 --all

python main.py schedule-generate 1
python main.py fixtures 1
python main.py result 1 2 1
python main.py standings 1
```

## Commands

| Command | Description |
|---------|-------------|
| `python main.py` | Interactive menu |
| `player-add <names...>` | Register one or more players |
| `player-add --names "A,B,C"` | Register players from comma-separated list |
| `player-add --file players.txt` | Register players from file (one name per line) |
| `player-list` | List all players |
| `season-create <name> <year>` | Create a season |
| `season-list` | List seasons and rosters |
| `season-add-players <id> <names...>` | Add multiple players to season |
| `season-add-players <id> --all` | Add every registered player to season |
| `season-remove-player <id> <player_id>` | Remove player from season |
| `schedule-generate <id> [--force]` | Generate all matchdays |
| `schedule-round <id>` | Add one extra matchday |
| `fixtures <id>` | Show all fixtures |
| `result <match_id> <score_a> <score_b>` | Record result |
| `standings <id>` | Show league table |

Data is stored in `~/.padel_league/league.db` by default. Override with `--db /path/to/file.db`.

## Project structure

```
Padel/
├── main.py                 # Entry point
└── padel_league/
    ├── models.py           # Data types
    ├── storage.py          # SQLite persistence
    ├── league.py           # Scheduling & standings
    └── cli.py              # CLI & interactive menu
```

## Requirements

Python 3.10+ (stdlib only — no pip install needed).
