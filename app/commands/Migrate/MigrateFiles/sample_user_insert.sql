INSERT IGNORE INTO `user`(
    `id`, `username`, `first_name`, `last_name`, `date_created`
)
VALUES (
    1, 'admin', 'Marcel', 'Kox', UTC_TIMESTAMP()
);