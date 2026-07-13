<?php

use App\Http\Controllers\Admin\ClubController;
use App\Http\Controllers\Admin\MatchController;
use App\Http\Controllers\Admin\SeasonController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\LeagueController;
use Illuminate\Support\Facades\Route;

// Invitation accept (public — user may not have account yet)
Route::get('/invite/{token}', [InvitationController::class, 'show'])->name('invite.accept');
Route::post('/invite/{token}', [InvitationController::class, 'accept']);

// Authenticated users only
Route::middleware('auth')->group(function () {

    // Club selection landing (if member of multiple clubs)
    Route::get('/', [LeagueController::class, 'home'])->name('home');

    // Club league views — any member of the club
    Route::middleware('club.member')->prefix('clubs/{club}')->name('club.')->group(function () {
        Route::get('/', [LeagueController::class, 'index'])->name('league');
        Route::get('/season/{season}/singles', [LeagueController::class, 'singles'])->name('singles');
        Route::get('/season/{season}/doubles', [LeagueController::class, 'doubles'])->name('doubles');
        Route::get('/season/{season}/fixtures', [LeagueController::class, 'fixtures'])->name('fixtures');
        Route::get('/schedule', [AvailabilityController::class, 'index'])->name('schedule');
        Route::post('/schedule', [AvailabilityController::class, 'toggle'])->name('schedule.toggle');
    });

    // Club admin panel — must be club admin
    Route::middleware('club.admin')->prefix('clubs/{club}/admin')->name('admin.')->group(function () {

        // Members & invitations
        Route::get('members', [ClubController::class, 'inviteIndex'])->name('members');
        Route::post('members/invite', [ClubController::class, 'inviteStore'])->name('members.invite');
        Route::delete('members/invite/{invitation}', [ClubController::class, 'inviteDestroy'])->name('members.invite.destroy');
        Route::delete('members/{user}', [ClubController::class, 'removeMember'])->name('members.remove');
        Route::patch('members/{user}/promote', [ClubController::class, 'promoteMember'])->name('members.promote');
        Route::patch('members/{user}/demote', [ClubController::class, 'demoteMember'])->name('members.demote');
        Route::patch('members/{user}/plays', [ClubController::class, 'updatePlays'])->name('members.plays');

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

    // Super-admin — manage clubs themselves
    Route::middleware('admin')->prefix('super')->name('super.')->group(function () {
        Route::get('clubs', [ClubController::class, 'index'])->name('clubs.index');
        Route::get('clubs/create', [ClubController::class, 'create'])->name('clubs.create');
        Route::post('clubs', [ClubController::class, 'store'])->name('clubs.store');
        Route::delete('clubs/{club}', [ClubController::class, 'destroy'])->name('clubs.destroy');
    });
});

require __DIR__.'/auth.php';
