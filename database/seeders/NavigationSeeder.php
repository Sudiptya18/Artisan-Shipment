<?php

namespace Database\Seeders;

use App\Models\Navigation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NavigationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete all navigations
        Navigation::query()->delete();
        
        // Reset AUTO_INCREMENT to start from 1
        DB::statement('ALTER TABLE navigations AUTO_INCREMENT = 1');

            $items = [
                [
                    'key' => 'dashboard',
                    'title' => 'Dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'route' => 'dashboard',
                    'children' => [],
                ],
                [
                    'key' => 'settings',
                    'title' => 'Settings',
                    'icon' => 'fas fa-cog',
                    'children' => [
                        [
                            'key' => 'user-registration',
                            'title' => 'User Registration',
                            'route' => 'user-registration',
                        ],
                        [
                            'key' => 'user-page-permission',
                            'title' => 'User Page Permission',
                            'route' => 'user-page-permission',
                        ],
                        [
                            'key' => 'forget-password',
                            'title' => 'Forget Password',
                            'route' => 'forget-password',
                        ],
                        [
                            'key' => 'change-password',
                            'title' => 'Change Password',
                            'route' => 'change-password',
                        ],
                    ],
                ],
                [
                    'key' => 'layouts',
                    'title' => 'Layouts',
                    'icon' => 'fas fa-columns',
                    'children' => [
                        [
                            'key' => 'layout-static',
                            'title' => 'Static Navigation',
                            'route' => 'layout-static',
                        ],
                        [
                            'key' => 'layout-sidenav-light',
                            'title' => 'Light Sidenav',
                            'route' => 'layout-sidenav-light',
                        ],
                    ],
                ],
                [
                    'key' => 'products',
                    'title' => 'Products',
                    'icon' => 'fas fa-box',
                    'children' => [
                        [
                            'key' => 'products-create',
                            'title' => 'Create Product',
                            'route' => 'products-create',
                        ],
                        [
                            'key' => 'products-list',
                            'title' => 'Product List',
                            'route' => 'products-list',
                        ],
                        [
                            'key' => 'products-edit',
                            'title' => 'Edit Product',
                            'route' => 'products-edit',
                        ],
                        [
                            'key' => 'brands',
                            'title' => 'Add Brands',
                            'route' => 'brands',
                        ],
                        [
                            'key' => 'categories',
                            'title' => 'Add Categories',
                            'route' => 'categories',
                        ],
                        [
                            'key' => 'formats',
                            'title' => 'Add Format',
                            'route' => 'formats',
                        ],
                        [
                            'key' => 'origins',
                            'title' => 'Add Country of Origin',
                            'route' => 'origins',
                        ],
                        [
                            'key' => 'products-multiple-create',
                            'title' => 'Create Multiple Products',
                            'route' => 'products-multiple-create',
                        ],
                        [
                            'key' => 'hscodes',
                            'title' => 'HS Code',
                            'route' => 'hscodes',
                        ],
                        [
                            'key' => 'ports',
                            'title' => 'Port',
                            'route' => 'ports',
                        ],
                        [
                            'key' => 'titles',
                            'title' => 'Shipment Title',
                            'route' => 'titles',
                        ],
                        [
                            'key' => 'groups',
                            'title' => 'Shipment Group',
                            'route' => 'groups',
                        ],
                    ],
                ],
                [
                    'key' => 'auth',
                    'title' => 'Authentication',
                    'icon' => 'fas fa-lock',
                    'children' => [
                        [
                            'key' => 'login',
                            'title' => 'Login',
                            'route' => 'login',
                        ],
                        [
                            'key' => 'register',
                            'title' => 'Register',
                            'route' => 'register',
                        ],
                        [
                            'key' => 'password',
                            'title' => 'Forgot Password',
                            'route' => 'password',
                        ],
                    ],
                ],
                [
                    'key' => 'errors',
                    'title' => 'Error Pages',
                    'icon' => 'fas fa-triangle-exclamation',
                    'children' => [
                        [
                            'key' => 'error-401',
                            'title' => '401 Page',
                            'route' => 'error-401',
                        ],
                        [
                            'key' => 'error-404',
                            'title' => '404 Page',
                            'route' => 'error-404',
                        ],
                        [
                            'key' => 'error-500',
                            'title' => '500 Page',
                            'route' => 'error-500',
                        ],
                    ],
                ],
                [
                    'key' => 'charts',
                    'title' => 'Charts',
                    'icon' => 'fas fa-chart-area',
                    'route' => 'charts',
                    'children' => [],
                ],
                [
                    'key' => 'tables',
                    'title' => 'Tables',
                    'icon' => 'fas fa-table',
                    'route' => 'tables',
                    'children' => [],
                ],
            ];

            $order = 0;
            foreach ($items as $item) {
                $children = $item['children'] ?? [];
                unset($item['children']);

                $navigation = Navigation::create([
                    'order_by' => $order++,
                    'is_enabled' => true,
                    'is_visible' => true,
                    ...$item,
                ]);

                $childOrder = 0;
                foreach ($children as $child) {
                    Navigation::create([
                        'parent_id' => $navigation->id,
                        'order_by' => $childOrder++,
                        'is_enabled' => true,
                        'is_visible' => true,
                        ...$child,
                    ]);
                }
            }
    }
}
