<?php

use App\Http\Controllers\Admin\MatchController;
use App\Http\Controllers\Admin\PlayerController;
use App\Http\Controllers\Admin\SeasonController;
use App\Http\Controllers\LeagueController;
use Illuminate\Support\Facades\Route;

// Public league views
Route::get('/', [LeagueController::class, 'index'])->name('home');
Route::get('/season/{season}', [LeagueController::class, 'season'])->name('league.season');

// Admin routes — auth + admin middleware
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Players
    Route::get('players', [PlayerController::class, 'index'])->name('players.index');
    Route::get('players/create', [PlayerController::class, 'create'])->name('players.create');
    Route::post('players', [PlayerController::class, 'store'])->name('players.store');
    Route::get('players/{player}/edit', [PlayerController::class, 'edit'])->name('players.edit');
    Route::patch('players/{player}', [PlayerController::class, 'update'])->name('players.update');
    Route::delete('players/{player}', [PlayerController::class, 'destroy'])->name('players.destroy');

    // Seasons
    Route::get('seasons', [SeasonController::class, 'index'])->name('seasons.index');
    Route::get('seasons/create', [SeasonController::class, 'create'])->name('seasons.create');
    Route::post('seasons', [SeasonController::class, 'store'])->name('seasons.store');
    Route::get('seasons/{season}', [SeasonController::class, 'show'])->name('seasons.show');
    Route::post('seasons/{season}/activate', [SeasonController::class, 'activate'])->name('seasons.activate');
    Route::delete('seasons/{season}', [SeasonController::class, 'destroy'])->name('seasons.destroy');
    Route::post('seasons/{season}/randomize-pairs', [SeasonController::class, 'randomizePairs'])->name('seasons.randomize-pairs');
    Route::get('seasons/{season}/pairs/{pair}/edit', [SeasonController::class, 'editPair'])->name('seasons.pairs.edit');
    Route::patch('seasons/{season}/pairs/{pair}', [SeasonController::class, 'updatePair'])->name('seasons.pairs.update');
    Route::delete('seasons/{season}/pairs/{pair}', [SeasonController::class, 'destroyPair'])->name('seasons.pairs.destroy');
    Route::post('seasons/{season}/pairs', [SeasonController::class, 'storePair'])->name('seasons.pairs.store');

    // Singles matches
    Route::get('seasons/{season}/singles/create', [MatchController::class, 'createSingles'])->name('matches.singles.create');
    Route::post('seasons/{season}/singles', [MatchController::class, 'storeSingles'])->name('matches.singles.store');
    Route::get('seasons/{season}/singles/{match}/edit', [MatchController::class, 'editSingles'])->name('matches.singles.edit');
    Route::patch('seasons/{season}/singles/{match}', [MatchController::class, 'updateSingles'])->name('matches.singles.update');
    Route::delete('seasons/{season}/singles/{match}', [MatchController::class, 'destroySingles'])->name('matches.singles.destroy');

    // Doubles matches
    Route::get('seasons/{season}/doubles/create', [MatchController::class, 'createDoubles'])->name('matches.doubles.create');
    Route::post('seasons/{season}/doubles', [MatchController::class, 'storeDoubles'])->name('matches.doubles.store');
    Route::get('seasons/{season}/doubles/{match}/edit', [MatchController::class, 'editDoubles'])->name('matches.doubles.edit');
    Route::patch('seasons/{season}/doubles/{match}', [MatchController::class, 'updateDoubles'])->name('matches.doubles.update');
    Route::delete('seasons/{season}/doubles/{match}', [MatchController::class, 'destroyDoubles'])->name('matches.doubles.destroy');
});

require __DIR__.'/auth.php';
