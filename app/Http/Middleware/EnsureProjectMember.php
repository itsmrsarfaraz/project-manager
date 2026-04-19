<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProjectMember
{
    /**
     * Handle an incoming request.
     *
     * This middleware runs on every route that has a {project} parameter.
     * It checks that the authenticated user is a member of that project
     * BEFORE any controller or policy runs.
     *
     * @param  string  $minimumRole  Optional: enforce a minimum role level
     */
    public function handle(
        Request $request,
        Closure $next,
        string $minimumRole = 'member' // default: any member can pass
    ): Response {
        // Get the project from route model binding
        // The route parameter name must be 'project'
        $project = $request->route('project');

        // If there's no project in the route, just continue
        // (this middleware might be applied to a group that has non-project routes)
        if (! $project instanceof Project) {
            return $next($request);
        }

        $user = $request->user();

        // Should never happen since we're inside auth middleware,
        // but be explicit — never assume
        if (! $user) {
            abort(401, 'You must be logged in.');
        }

        // Get the user's role on this specific project
        $role = $user->roleOn($project);

        // Not a member at all → 403
        if ($role === null) {
            abort(403, 'You are not a member of this project.');
        }

        // Role hierarchy — higher index = more permissions
        $hierarchy = ['member', 'manager', 'owner'];

        $userRoleIndex    = array_search($role, $hierarchy);
        $requiredRoleIndex = array_search($minimumRole, $hierarchy);

        // If the required role isn't in our hierarchy, it's a configuration mistake
        if ($requiredRoleIndex === false) {
            abort(500, "Invalid role requirement: '{$minimumRole}'");
        }

        // Check if user's role meets the minimum requirement
        if ($userRoleIndex < $requiredRoleIndex) {
            abort(403, "You need at least the '{$minimumRole}' role to do this.");
        }

        return $next($request);
    }
}
