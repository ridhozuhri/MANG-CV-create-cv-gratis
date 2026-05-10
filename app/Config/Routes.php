<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/test', 'Home::test');
$routes->get('/buat-cv', 'Cv::wizard');
$routes->get('/buat-cv/step/(:num)', 'Cv::step/$1');
$routes->post('/api/autosave', 'Api::autosave');
$routes->post('/api/upload-photo', 'Api::uploadPhoto');
$routes->post('/api/delete-photo', 'Api::deletePhoto');
$routes->post('/api/preview', 'Api::preview');
$routes->get('/api/preview', 'Api::preview');
$routes->post('/api/preview-draft', 'Api::previewDraft');
$routes->post('/api/switch-template', 'Api::switchTemplate');
$routes->get('/api/templates', 'Api::getTemplates');
$routes->post('/api/check-overflow', 'Api::checkOverflow');
$routes->get('/api/check-overflow', 'Api::checkOverflow');
$routes->get('/api/capacity', 'Api::getCapacity');
$routes->get('/cron/cleanup', 'Cron::cleanup');
$routes->get('/cron/status', 'Cron::status');
$routes->get('/media/photo', 'Media::photo');
$routes->get('/preview', 'Preview::index');
$routes->get('/export/pdf', 'Export::pdf');
$routes->get('/export/txt', 'Export::txt');
$routes->get('/sitemap.xml', 'Home::sitemap');
$routes->get('/robots.txt', 'Home::robots');
$routes->get('/export/json', 'Export::json');
$routes->get('/og-default.png', 'Home::ogDefault');
