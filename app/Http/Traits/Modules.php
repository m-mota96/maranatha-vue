<?php

namespace App\Http\Traits;
use App\Models\Module;
use App\Models\User;

trait Modules {
    public static function module($target) {
        $module = Module::with(['dad.dad'])->where('target', $target)
        ->whereHas('users', function($query) {
            $query->where('user_id', auth()->user()->id);
        })
        ->first();
        return $module;
    }

    public static function modulesMenu() {
        $modules = Module::with(['submodules.dad', 'submodules' => function($query) {
            $query->with(['submodules.dad.dad', 'submodules' => function($query2) {
                $query2->whereHas('users', function($query3) {
                    $query3->where('user_id', auth()->user()->id);
                });
            }])->whereHas('users', function($query2) {
                $query2->where('user_id', auth()->user()->id);
            });
        }])->whereHas('users', function($query) {
            $query->where('user_id', auth()->user()->id);
        })
        ->where('status', 1)->where('module_id', null)
        ->get();
        return $modules;
    }

    public static function allModulesMenu() {
        $modules = Module::with(['submodules.dad', 'submodules' => function($query) {
            $query->with(['submodules.dad.dad', 'submodules']);
        }])
        ->where('status', 1)->where('module_id', null)
        ->get();
        return $modules;
    }

    public static function allModules() {
        $modules = Module::with([
            'dad',
            // 'permissions',
            'submodules.dad',
            // 'submodules.permissions',
            'submodules.submodules.dad',
            // 'submodules.submodules.permissions'
        ])->get();
        return $modules;
    }

    public static function modulesNewMenu() {
        $modules = Module::where('module_id', null)->orWhereRaw('(module_id IS NOT NULL AND target IS NULL)')->orderBy('name')->get();
        return $modules;
    }

    public static function userModules($userId) {
        $user = User::with(['modules'])->find($userId);
        return $user->modules;
    }
}