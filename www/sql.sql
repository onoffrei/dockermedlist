TRUNCATE TABLE `users`;
INSERT INTO `users` (id,email				, password								, date			, nume		, uri)
VALUES 				(1,'medlistro@gmail.com'	, '21232f297a57a5a743894a0e4a801fc3'	, '2018-01-01'	,'admin'	,'admin');

TRUNCATE TABLE `spitale_users`;
INSERT INTO `spitale_users` (user	, level	)
VALUES 						(1		, 4);

TRUNCATE TABLE `specializari_user_spitale`;

TRUNCATE TABLE `spitale`;
TRUNCATE TABLE `legenda`;
TRUNCATE TABLE `planificare`;
TRUNCATE TABLE `programari`;
TRUNCATE TABLE `detaliiservicii`;
TRUNCATE TABLE `images`;
TRUNCATE TABLE `spitale_images`;
TRUNCATE TABLE `mesaje`;
TRUNCATE TABLE `browser`;
TRUNCATE TABLE `localitati_spitale`;
TRUNCATE TABLE `usersstatus`;