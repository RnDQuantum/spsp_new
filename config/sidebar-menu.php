<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sidebar Menu Configuration
    |--------------------------------------------------------------------------
    |
    | This file defines the structure and configuration for the sidebar menu.
    | You can customize menu items, icons, routes, and visibility rules here.
    |
    */

    'dashboard_admin' => [
        'icon' => 'fa-solid fa-house-laptop',
        'title' => 'Beranda Admin',
        'route' => 'dashboard-admin',
        'role' => 'admin',
    ],

    'dashboard' => [
        'icon' => 'fa-solid fa-house',
        'title' => 'Beranda',
        'route' => 'dashboard',
    ],

    'shortlist' => [
        'icon' => 'fa-solid fa-users',
        'title' => 'Daftar Peserta',
        'route' => 'shortlist',
    ],

    'custom_standards' => [
        'icon' => 'fa-solid fa-chart-line',
        'title' => 'Tambah Standar',
        'route' => 'custom-standards.index',
    ],

    'divider_1' => [
        'type' => 'divider',
    ],

    'individual_report' => [
        'type' => 'dropdown',
        'icon' => 'fa-solid fa-file-invoice',
        'title' => 'Individual Report',
        'requires_participant' => true,
        'active' => 'individual.*', // used internally for matching
        'items' => [
            [
                'title' => 'General Matching',
                'route' => 'general_matching',
            ],
            [
                'title' => 'General Mapping',
                'route' => 'general_mapping',
            ],
            [
                'title' => 'General Psy Mapping',
                'route' => 'general_psy_mapping',
            ],
            [
                'title' => 'General MC Mapping',
                'route' => 'general_mc_mapping',
            ],
            [
                'title' => 'Spider Plot',
                'route' => 'spider_plot',
            ],
            [
                'title' => 'Ringkasan MC Mapping',
                'route' => 'ringkasan_mc_mapping',
            ],
            [
                'title' => 'Ringkasan Asesmen',
                'route' => 'ringkasan_assessment',
            ],
            [
                'title' => 'Laporan Individu',
                'route' => 'final_report',
            ],
        ],
    ],

    'general_report' => [
        'type' => 'dropdown',
        'icon' => 'fa-solid fa-chart-pie',
        'title' => 'General Report',
        'items' => [
            [
                'title' => 'Ranking Psy Mapping',
                'route' => 'ranking-psy-mapping',
            ],
            [
                'title' => 'Ranking MC Mapping',
                'route' => 'ranking-mc-mapping',
            ],
            [
                'title' => 'Ranking Ringkasan Asesmen',
                'route' => 'rekap-ranking-assessment',
            ],
            [
                'title' => 'Statistik',
                'route' => 'statistic',
            ],
            [
                'title' => 'Training Recommendation',
                'route' => 'training-recommendation',
            ],
            [
                'title' => 'Talent Pool Management',
                'route' => 'talentpool',
            ],
            [
                'title' => 'MMPI',
                'route' => 'general-report.mmpi',
            ],
        ],
    ],

    'standard_mc' => [
        'icon' => 'fa-solid fa-sliders',
        'title' => 'Standar MC Mapping',
        'route' => 'standard-mc',
    ],

    'standard_psikometrik' => [
        'icon' => 'fa-solid fa-sliders-h',
        'title' => 'Standar Potential Mapping',
        'route' => 'standard-psikometrik',
    ],

    'laporan_alat_tes' => [
        'icon' => 'fa-solid fa-square-poll-vertical',
        'title' => 'Laporan Alat Tes',
        'route' => 'laporan-alat-tes',
    ],
];
