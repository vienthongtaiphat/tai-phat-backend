<?php

return [
    'prefix' => 'graphql',
    'routes' => '{graphql_schema?}',
    'controllers' => \Rebing\GraphQL\GraphQLController::class . '@query',
    'middleware' => [],
    'route_group_attributes' => [],
    'default_schema' => 'default',
    'schemas' => [
        'default' => [
            'query' => [
                'user' => App\GraphQL\Queries\User\UserQuery::class,
            ],
            'mutation' => [
            ],
        ],
        'secret' => [
            'query' => [
                'Users' => App\GraphQL\Queries\User\UsersQuery::class,
                'User' => App\GraphQL\Queries\User\UserQuery::class,

                'CtvUsers' => App\GraphQL\Queries\CtvUser\CtvUsersQuery::class,
                'CtvUser' => App\GraphQL\Queries\CtvUser\CtvUserQuery::class,

                'Packs' => App\GraphQL\Queries\Pack\PacksQuery::class,
                'Pack' => App\GraphQL\Queries\Pack\PackQuery::class,

                'FileUploadHistory' => App\GraphQL\Queries\FileUploadHistory\FileUploadHistoryQuery::class,
                'FileUploadHistories' => App\GraphQL\Queries\FileUploadHistory\FileUploadHistoriesQuery::class,

                'Subscription' => App\GraphQL\Queries\Subscription\SubscriptionQuery::class,
                'Subscriptions' => App\GraphQL\Queries\Subscription\SubscriptionsQuery::class,

                'Branch' => App\GraphQL\Queries\Branch\BranchQuery::class,
                'Branches' => App\GraphQL\Queries\Branch\BranchesQuery::class,

                'SendOtpHistory' => App\GraphQL\Queries\SendOtpHistory\SendOtpHistoryQuery::class,
                'SendOtpHistories' => App\GraphQL\Queries\SendOtpHistory\SendOtpHistoriesQuery::class,

                'RefundHistoryList' => App\GraphQL\Queries\RefundHistory\RefundHistoryListQuery::class,

                'UpgradeHistoryList' => App\GraphQL\Queries\UpgradeHistory\ListQuery::class,

                'Logs' => App\GraphQL\Queries\Log\LogsQuery::class,
                'LogsTotal' => App\GraphQL\Queries\Log\LogsTotalQuery::class,
                'Log' => App\GraphQL\Queries\Log\LogQuery::class,

                'NewsList' => App\GraphQL\Queries\News\NewssQuery::class,
                'News' => App\GraphQL\Queries\News\NewsQuery::class,

                'DataLogs' => App\GraphQL\Queries\DataLog\LogsQuery::class,
                'DataLogsTotal' => App\GraphQL\Queries\DataLog\LogsTotalQuery::class,
                'DataLog' => App\GraphQL\Queries\DataLog\LogQuery::class,

                'PackCodeList' => App\GraphQL\Queries\PackCode\ListQuery::class,
                'PackCode' => App\GraphQL\Queries\PackCode\SingleQuery::class,

                'PackStoreList' => App\GraphQL\Queries\PackCodeStore\ListQuery::class,
                'PackStore' => App\GraphQL\Queries\PackCodeStore\SingleQuery::class,

                'ExtendPackList' => App\GraphQL\Queries\ExtendPack\ListQuery::class,
                'ExtendPack' => App\GraphQL\Queries\ExtendPack\SingleQuery::class,

                'ExtendPackStoreList' => App\GraphQL\Queries\ExtendPackStore\ListQuery::class,
                'ExtendPackStore' => App\GraphQL\Queries\ExtendPackStore\SingleQuery::class,

                'ExtendPackRequestList' => App\GraphQL\Queries\ExtendPackRequest\ListQuery::class,
                'ExtendPackRequest' => App\GraphQL\Queries\ExtendPackRequest\SingleQuery::class,

                'PackCodeRequestList' => App\GraphQL\Queries\PackCodeRequest\ListQuery::class,
                'PackCodeRequest' => App\GraphQL\Queries\PackCodeRequest\SingleQuery::class,

                'PayDebtRequestList' => App\GraphQL\Queries\PayDebtRequest\ListQuery::class,
                'PayDebtRequest' => App\GraphQL\Queries\PayDebtRequest\SingleQuery::class,

                'TopupRequestList' => App\GraphQL\Queries\TopupRequest\ListQuery::class,
                'TopupRequest' => App\GraphQL\Queries\TopupRequest\SingleQuery::class,

                'CallHistoryList' => App\GraphQL\Queries\CallHistory\ListQuery::class,
                'CallHistory' => App\GraphQL\Queries\CallHistory\SingleQuery::class,

                'BranchTargetList' => App\GraphQL\Queries\BranchTarget\ListQuery::class,
                'BranchTarget' => App\GraphQL\Queries\BranchTarget\SingleQuery::class,

                'ScanBalanceList' => App\GraphQL\Queries\ScanBalance\ListQuery::class,
                'ScanBalance' => App\GraphQL\Queries\ScanBalance\SingleQuery::class,
            ],
            'mutation' => [
                'createUser' => App\GraphQL\Mutations\User\CreateUserMutation::class,
                'deleteUsers' => App\GraphQL\Mutations\User\DeleteUserMutation::class,
                'updateUser' => App\GraphQL\Mutations\User\UpdateUserMutation::class,

                'createPackCode' => App\GraphQL\Mutations\PackCode\CreateMutation::class,
                'deletePackCode' => App\GraphQL\Mutations\PackCode\DeleteMutation::class,
                'updatePackCode' => App\GraphQL\Mutations\PackCode\UpdateMutation::class,

                'createBranch' => App\GraphQL\Mutations\Branch\CreateBranchMutation::class,
                'deleteBranches' => App\GraphQL\Mutations\Branch\DeleteBranchMutation::class,
                'updateBranch' => App\GraphQL\Mutations\Branch\UpdateBranchMutation::class,

                'updateSubscription' => App\GraphQL\Mutations\Subscription\UpdateSubscriptionMutation::class,
                'deleteSubscriptions' => App\GraphQL\Mutations\Subscription\DeleteSubscriptionMutation::class,
                'createSubscription' => App\GraphQL\Mutations\Subscription\CreateSubscriptionMutation::class,
                'divideSubscriptions' => App\GraphQL\Mutations\Subscription\DivideSubscriptionsToUsersMutation::class,
                'divideFileToUsers' => App\GraphQL\Mutations\Subscription\DivideFileToUsersMutation::class,

                'createPack' => App\GraphQL\Mutations\Pack\CreateMutation::class,
                'updatePack' => App\GraphQL\Mutations\Pack\UpdateMutation::class,
                'deletePack' => App\GraphQL\Mutations\Pack\DeleteMutation::class,

                'createNews' => App\GraphQL\Mutations\News\CreateMutation::class,
                'updateNews' => App\GraphQL\Mutations\News\UpdateMutation::class,
                'deleteNews' => App\GraphQL\Mutations\News\DeleteMutation::class,

                'createEvent' => App\GraphQL\Mutations\Event\CreateMutation::class,
                'updateEvent' => App\GraphQL\Mutations\Event\UpdateMutation::class,
                'deleteEvent' => App\GraphQL\Mutations\Event\DeleteMutation::class,

                'createScanBalance' => App\GraphQL\Mutations\ScanBalance\CreateMutation::class,
                'updateScanBalance' => App\GraphQL\Mutations\ScanBalance\UpdateMutation::class,
                'deleteScanBalance' => App\GraphQL\Mutations\ScanBalance\DeleteMutation::class,

                'createBranchTarget' => App\GraphQL\Mutations\BranchTarget\CreateMutation::class,
                'updateBranchTarget' => App\GraphQL\Mutations\BranchTarget\UpdateMutation::class,
                'deleteBranchTarget' => App\GraphQL\Mutations\BranchTarget\DeleteMutation::class,

                'createPackRequest' => App\GraphQL\Mutations\PackRequest\CreateMutation::class,
                'updatePackRequest' => App\GraphQL\Mutations\PackRequest\UpdateMutation::class,
                'deletePackRequest' => App\GraphQL\Mutations\PackRequest\DeleteMutation::class,

                'createPayDebtRequest' => App\GraphQL\Mutations\PayDebtRequest\CreateMutation::class,
                'updatePayDebtRequest' => App\GraphQL\Mutations\PayDebtRequest\UpdateMutation::class,
                'deletePayDebtRequest' => App\GraphQL\Mutations\PayDebtRequest\DeleteMutation::class,

                'createTopupRequest' => App\GraphQL\Mutations\TopupRequest\CreateMutation::class,
                'updateTopupRequest' => App\GraphQL\Mutations\TopupRequest\UpdateMutation::class,
                'deleteTopupRequest' => App\GraphQL\Mutations\TopupRequest\DeleteMutation::class,

                'createExtendPack' => App\GraphQL\Mutations\ExtendPack\CreateMutation::class,
                'updateExtendPack' => App\GraphQL\Mutations\ExtendPack\UpdateMutation::class,
                'deleteExtendPack' => App\GraphQL\Mutations\ExtendPack\DeleteMutation::class,

                'createExtendPackRequest' => App\GraphQL\Mutations\ExtendPackRequest\CreateMutation::class,
                'updateExtendPackRequest' => App\GraphQL\Mutations\ExtendPackRequest\UpdateMutation::class,
                'deleteExtendPackRequest' => App\GraphQL\Mutations\ExtendPackRequest\DeleteMutation::class,
                'uploadExtendFile' => App\GraphQL\Mutations\ExtendPackRequest\UploadFileMutation::class,

                'createFileUploadedHistory' => App\GraphQL\Mutations\FileUploadedHistory\CreateFileUploadedHistoryMutation::class,
                'updateFileUploadedHistory' => App\GraphQL\Mutations\FileUploadedHistory\UpdateFileMutation::class,
                'deleteFileUploadedHistory' => App\GraphQL\Mutations\FileUploadedHistory\DeleteFileUploadedMutation::class,

                'createUpgradeHistory' => App\GraphQL\Mutations\UpgradeHistory\CreateMutation::class,
                'deleteUpgradeHistory' => App\GraphQL\Mutations\UpgradeHistory\DeleteMutation::class,
                'updateUpgradeHistory' => App\GraphQL\Mutations\UpgradeHistory\UpdateMutation::class,
                'uploadUpgradeFile' => App\GraphQL\Mutations\UpgradeHistory\UploadFileMutation::class,
            ],
            'middleware' => ['api'],
        ],
    ],
    'types' => [
        'CtvUser' => App\GraphQL\Types\CtvUserType::class,
        'User' => App\GraphQL\Types\UserType::class,
        'Branch' => App\GraphQL\Types\BranchType::class,
        'Pack' => App\GraphQL\Types\PackType::class,
        'FileUploadedHistory' => App\GraphQL\Types\FileUploadedHistoryType::class,
        'Subscription' => App\GraphQL\Types\SubscriptionType::class,
        'BranchInputType' => App\GraphQL\Types\BranchInputType::class,
        'SubscriptionQuery' => App\GraphQL\Types\SubscriptionQueryType::class,
        'SendOtpHistory' => App\GraphQL\Types\SendOtpHistoryType::class,
        'RefundHistory' => App\GraphQL\Types\RefundHistoryType::class,
        'UpgradeHistory' => App\GraphQL\Types\UpgradeHistoryType::class,
        'RefundAccount' => App\GraphQL\Types\RefundAccountType::class,
        'Log' => App\GraphQL\Types\LogType::class,
        'DataLog' => App\GraphQL\Types\DataLogType::class,
        'LogType' => App\GraphQL\Types\LogTypeType::class,
        'PackCodeStore' => App\GraphQL\Types\PackCodeStoreType::class,
        'PackCodeHistory' => App\GraphQL\Types\PackCodeHistoryType::class,
        'PackCodeRequest' => App\GraphQL\Types\PackCodeRequestType::class,

        'ExtendPackStore' => App\GraphQL\Types\ExtendPackStoreType::class,
        'ExtendPackRequest' => App\GraphQL\Types\ExtendPackRequestType::class,

        'PayDebtRequest' => App\GraphQL\Types\PayDebtRequestType::class,
        'Assign' => App\GraphQL\Types\AssignType::class,
        'ExtendPack' => App\GraphQL\Types\ExtendPackType::class,
        'TopupRequest' => App\GraphQL\Types\TopupRequestType::class,
        'UpgradeHistoryInput' => App\GraphQL\Types\UpgradeHistoryInputType::class,
        'CallHistory' => App\GraphQL\Types\CallHistoryType::class,
        'News' => App\GraphQL\Types\NewsType::class,
        'BranchTarget' => App\GraphQL\Types\BranchTargetType::class,
        'Event' => App\GraphQL\Types\EventType::class,
        'ScanBalance' => App\GraphQL\Types\ScanBalanceType::class,
    ],
    'lazyload_types' => false,
    'error_formatter' => ['\Rebing\GraphQL\GraphQL', 'formatError'],

    'errors_handler' => ['\Rebing\GraphQL\GraphQL', 'handleErrors'],

    // You can set the key, which will be used to retrieve the dynamic variables
    'params_key' => 'variables',

    'security' => [
        'query_max_complexity' => null,
        'query_max_depth' => null,
        'disable_introspection' => false,
    ],

    'pagination_type' => \Rebing\GraphQL\Support\PaginationType::class,

    'graphiql' => [
        'prefix' => '/graphiql',
        'controller' => \Rebing\GraphQL\GraphQLController::class . '@graphiql',
        'middleware' => [],
        'view' => 'graphql::graphiql',
        'display' => env('ENABLE_GRAPHIQL', false),
    ],

    'defaultFieldResolver' => null,

    'headers' => [],

    'json_encoding_options' => 0,

    'apg' => [
        'enable' => true,
    ],

    'detect_unused_variables' => true,
];
