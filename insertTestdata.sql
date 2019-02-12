SET NAMES utf8mb4;

INSERT INTO movie_years(year) VALUES(2019);

INSERT INTO `movies` (`name`, `date`, `trailerLink`, `posterLink`, `bookedTickets`, `movie_year_id`, `worker_id`, `emergency_worker_id`, `created_at`, `updated_at`) VALUES
('Inception',	'2019-07-04',	'https://youtube.com',	'https://m.media-amazon.com/images/M/MV5BMTI4NDk5NDgzN15BMl5BanBnXkFtZTcwNzU0OTk1Mw@@._V1_SY1000_CR0,0,685,1000_AL_.jpg',	0,	1,	NULL,	NULL,	'2019-02-05 21:29:13',	'2019-02-06 12:18:20'),
('Titanic',	'2019-07-05',	'https://youtube.com',	'https://m.media-amazon.com/images/M/MV5BMDdmZGU3NDQtY2E5My00ZTliLWIzOTUtMTY4ZGI1YjdiNjk3XkEyXkFqcGdeQXVyNTA4NzY1MzY@._V1_SY1000_CR0,0,671,1000_AL_.jpg',	0,	1,	NULL,	NULL,	'2019-02-05 21:30:35',	'2019-02-06 12:18:46'),
('Avatar',	'2019-07-06',	'https://youtube.com',	'https://www.foxmovies.com/s3/dev-temp/en-US/__5603af15335dd-4a38d2eebe46ae0c1e7104d341e9ddef6b8c4794-00f8fcfabf9f0d18.jpg',	0,	1,	NULL,	NULL,	'2019-02-05 21:31:44',	'2019-02-05 21:31:44'),
('Wolf of Wall Street',	'2019-07-06',	'https://youtube.com',	'http://www.danielyeow.com/wp-content/uploads/TheWolfofWallStreet-poster.jpg',	0,	1,	NULL,	NULL,	'2019-02-05 21:32:32',	'2019-02-05 21:32:32');