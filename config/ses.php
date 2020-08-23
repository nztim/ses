<?php

return [
    'arns'                => ['*'], // string[], filter for specific SNS ARNs, '*' is a wildcard
    'log_sns_subs'        => true,  // bool
    'sns_subs_recipient'  => null,  // email|null
];
