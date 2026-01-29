<?php

namespace App\Core\Theme;

class Users
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        // Apply role changes on init
        add_action('init', [$this, 'applyRoles'], 20);
        // Ensure displayed role names are translated/overridden where WP uses translate_user_role
        add_filter('translate_user_role', [$this, 'translateUserRole'], 10, 2);
        // Limit editable roles in user edit screens
        add_filter('editable_roles', [$this, 'filterEditableRoles']);
    }

    public function applyRoles(): void
    {
        if (empty($this->config['roles_available']) || !is_array($this->config['roles_available'])) {
            return;
        }

        global $wp_roles;
        if (!isset($wp_roles)) {
            if (class_exists('WP_Roles')) {
                $wp_roles = new \WP_Roles();
            } else {
                return;
            }
        }

        $allowed = $this->config['roles_available'];

        // Rename allowed roles or remove those explicitly set to false
        foreach ($allowed as $role => $label) {
            if (!isset($wp_roles->roles[$role])) {
                continue;
            }

            // If label is boolean false => hide/remove role
            if ($label === false) {
                // Don't call remove_role (persistent). Only remove from runtime lists so role is hidden in admin.
                unset($wp_roles->roles[$role], $wp_roles->role_names[$role]);
                continue;
            }

            // If label is a non-empty string => apply rename
            if (is_string($label) && $label !== '') {
                $wp_roles->roles[$role]['name'] = $label;
                $wp_roles->role_names[$role] = $label;
            }
        }

        // Remove roles not present in the allowed list
        foreach (array_keys($wp_roles->roles) as $role) {
            if (!array_key_exists($role, $allowed)) {
                // Only hide roles at runtime; avoid persistent deletion
                unset($wp_roles->roles[$role], $wp_roles->role_names[$role]);
            }
        }
    }

    /**
     * Filter editable roles shown in user edit screens to the allowed set and apply labels.
     */
    public function filterEditableRoles(array $roles): array
    {
        if (empty($this->config['roles_available']) || !is_array($this->config['roles_available'])) {
            return $roles;
        }

        $allowed = $this->config['roles_available'];
        $filtered = [];

        foreach ($allowed as $role => $label) {
            // Skip roles explicitly hidden
            if ($label === false) {
                continue;
            }

            if (isset($roles[$role])) {
                if (is_string($label) && $label !== '') {
                    $roles[$role]['name'] = $label;
                }
                $filtered[$role] = $roles[$role];
            }
        }

        return $filtered;
    }

    /**
     * Ensure WP displays the configured label for roles where translate_user_role is used.
     */
    public function translateUserRole(string $translated, string $role): string
    {
        if (empty($this->config['roles_available']) || !is_array($this->config['roles_available'])) {
            return $translated;
        }

        if (!array_key_exists($role, $this->config['roles_available'])) {
            return $translated;
        }

        $label = $this->config['roles_available'][$role];

        // If explicitly hidden, return empty to hide display
        if ($label === false) {
            return '';
        }

        return is_string($label) && $label !== '' ? $label : $translated;
    }

}