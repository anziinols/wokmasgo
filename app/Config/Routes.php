<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Landing Page Route
$routes->get('/', 'Landing::index');

// Markdown Viewer route (client-side processing)
$routes->get('markdown-viewer', 'MarkdownViewer::index');

// Image Creator route (AI-powered image generation)
$routes->get('image-creator', 'ImageCreator::index');

// Future app routes (uncomment when ready to use)
// $routes->get('dashboard', 'Dashboard::index');
// $routes->get('users', 'Users::index');
// $routes->get('reports', 'Reports::index');
// $routes->get('settings', 'Settings::index');
// $routes->get('projects', 'Projects::index');
// $routes->get('calendar', 'Calendar::index');
// $routes->get('messages', 'Messages::index');
// $routes->get('analytics', 'Analytics::index');

// Additional routes (uncomment when ready to use)
// $routes->get('about', 'Pages::about');
// $routes->get('contact', 'Pages::contact');
// $routes->get('privacy', 'Pages::privacy');
// $routes->get('terms', 'Pages::terms');
// $routes->get('help', 'Pages::help');
