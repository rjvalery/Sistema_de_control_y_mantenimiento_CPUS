import os

web_file = 'routes/web.php'
api_file = 'routes/api.php'

with open(web_file, 'r', encoding='utf-8') as f:
    web_content = f.read()

# Replace auth middleware to auth:sanctum
web_content = web_content.replace("middleware('auth')", "middleware('auth:sanctum')")
web_content = web_content.replace("middleware(['auth', ", "middleware(['auth:sanctum', ")

with open(api_file, 'w', encoding='utf-8') as f:
    f.write(web_content)

with open(web_file, 'w', encoding='utf-8') as f:
    f.write('''<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'API is running']);
});
''')

print("Moved routes to api.php and cleaned web.php")
