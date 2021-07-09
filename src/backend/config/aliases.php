<?php

return [
    // modules here
    '@authModule' => '@backend/modules/auth',
    '@confModule' => '@backend/modules/conf',
    '@usersModule' => '@backend/modules/users',
    '@coreModule' => '@backend/modules/core',
    '@reportsModule' => '@backend/modules/reports',
    '@dashboardModule' => '@backend/modules/dashboard',
    '@helpModule' => '@backend/modules/help',
    '@accountingModule' => '@backend/modules/accounting',
    '@productModule' => '@backend/modules/product',
    '@savingModule' => '@backend/modules/saving',
    '@loanModule' => '@backend/modules/loan',
    '@subscriptionModule' => '@backend/modules/subscription',
    '@paymentModule' => '@backend/modules/payment',
    '@workflowModule' => '@backend/modules/workflow',
    //'@workersModule' => '@backend/modules/workers',
    // other paths
    '@redactor' => '@uploads/redactor',
    '@redactorFiles' => '@redactor/files',
    '@redactorImages' => '@redactor/images',
    '@backendAssets' => '@backend/assets',
];