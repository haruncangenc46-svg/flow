<?php
// In a real-world application, this data would come from a database.
$users = [
    'admin' => [
        'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
        'role' => 'admin'
    ],
    'muhasebe' => [
        'password_hash' => password_hash('muhasebe123', PASSWORD_DEFAULT),
        'role' => 'muhasebe'
    ],
    'stok' => [
        'password_hash' => password_hash('stok123', PASSWORD_DEFAULT),
        'role' => 'stok'
    ]
];
?>
