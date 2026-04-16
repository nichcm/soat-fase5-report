<?php

use Illuminate\Support\Facades\Route;

Route::get("ping", function () {
    return response()->json([
        "err" => false,
        "msg" => "pong",
        "service" => env("APP_NAME", "APP_ENV não definida"),
    ]);
});

Route::get("/status/{protocol_uuid}", [
    \App\Http\Controllers\ReportConnectionController::class,
    "getReportStatus",
]);

Route::get("/report/{protocol_uuid}", [
    \App\Http\Controllers\ReportConnectionController::class,
    "getReport",
]);

Route::fallback(
    fn() => response()->json([
        "err" => true,
        "msg" => "Recurso não encontrado",
    ]),
);
