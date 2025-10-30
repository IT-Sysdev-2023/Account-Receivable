<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function setTheme(Request $request)
    {
        $request->validate([
            'theme' => 'required|string|in:Light,Light Blue,Light Gray,Dark,Dark Blue,Dark Green,Zen Mist,Eucalyptus,Lavender Haze,Seafoam,Parchment,Beige,Midnight Mist,Deep Forest,Twilight Lavender,Abyssal Teal,Charcoal Parchment,Light Coffee,Dark Coffee', // Add custom if needed
        ]);

        $user = $request->user();
        $user->theme = $request->theme;
        $user->save();

        return response()->json(['status' => 'ok']);
    }
}
