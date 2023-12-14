USE db_ecole;
create table users (
	id INT primary key auto_increment,
	first_name VARCHAR(255),
	last_name VARCHAR(255),
	email VARCHAR(255),
	gender VARCHAR(255),
	ip_address VARCHAR(20)
);
insert into users ( first_name, last_name, email, gender, ip_address) values ( 'Rosabella', 'Mattholie', 'rmattholie0@hubpages.com', 'Female', '2255.136.139.149');
insert into users ( first_name, last_name, email, gender, ip_address) values ( 'Shalne', 'Luciano', 'sluciano1@eepurl.com', 'Female', '184.45.8.39');
insert into users ( first_name, last_name, email, gender, ip_address) values ( 'Wolf', 'Ilem', 'wilem2@theatlantic.com', 'Male', '163.92.143.135');
insert into users ( first_name, last_name, email, gender, ip_address) values ( 'Kienan', 'O''Loghlen', 'kologhlen3@europa.eu', 'Male', '40.26.98.161');
insert into users ( first_name, last_name, email, gender, ip_address) values ( 'Boniface', 'Fayers', 'bfayers4@last.fm', 'Male', '223.87.14.213');
insert into users ( first_name, last_name, email, gender, ip_address) values ( 'Zackariah', 'Forlong', 'zforlong5@cbc.ca', 'Male', '67.207.67.172');
insert into users ( first_name, last_name, email, gender, ip_address) values ( 'Berry', 'Gilham', 'bgilham6@theatlantic.com', 'Female', '68.104.217.161');
insert into users ( first_name, last_name, email, gender, ip_address) values ( 'Caralie', 'Whittingham', 'cwhittingham7@senate.gov', 'Female', '184.226.116.242');
insert into users ( first_name, last_name, email, gender, ip_address) values ( 'Lanny', 'Witts', 'lwitts8@bing.com', 'Female', '37.1.23.254');
insert into users ( first_name, last_name, email, gender, ip_address) values ( 'Sauveur', 'Howler', 'showler9@com.com', 'Male', '154.169.2.39');
insert into users ( first_name, last_name, email, gender, ip_address) values ( 'Lyle', 'Droghan', 'ldroghana@studiopress.com', 'Male', '108.111.255.54');

create table etudiants (
	id INT primary key auto_increment,
	nom VARCHAR(255),
	prenom VARCHAR(255),
	date_inscription VARCHAR(255),
	genre VARCHAR(255),
	adresse VARCHAR(255),
	id_filiere INT,
	ville VARCHAR(255)
);

insert into etudiants ( nom, prenom, date_inscription, genre, adresse, id_filiere, ville) values ( 'Ax', 'Duprey', 'aduprey0@hc360.com', 'Male', 'Suite 76', 1, 'Granada');
insert into etudiants ( nom, prenom, date_inscription, genre, adresse, id_filiere, ville) values ( 'Francklin', 'Stanworth', 'fstanworth1@aol.com', 'Male', 'PO Box 25505', 2, 'Itapemirim');
insert into etudiants ( nom, prenom, date_inscription, genre, adresse, id_filiere, ville) values ( 'Grover', 'Caze', 'gcaze2@behance.net', 'Male', 'Room 1689', 1, 'Volochys’k');
insert into etudiants ( nom, prenom, date_inscription, genre, adresse, id_filiere, ville) values ( 'Lee', 'Papachristophorou', 'lpapachristophorou3@examiner.com', 'Female', 'Apt 13', 2, 'Międzyzdroje');
insert into etudiants ( nom, prenom, date_inscription, genre, adresse, id_filiere, ville) values ( 'Lonny', 'Fridaye', 'lfridaye4@wix.com', 'Male', 'Apt 491', 1, 'Or Yehuda');
insert into etudiants ( nom, prenom, date_inscription, genre, adresse, id_filiere, ville) values ( 'Anatol', 'McDell', 'amcdell5@hibu.com', 'Male', 'Suite 36', 2, 'Muricay');
insert into etudiants ( nom, prenom, date_inscription, genre, adresse, id_filiere, ville) values ( 'Haven', 'Rooney', 'hrooney6@vkontakte.ru', 'Male', 'Suite 48', 1, 'Camiri');
insert into etudiants ( nom, prenom, date_inscription, genre, adresse, id_filiere, ville) values ( 'Devland', 'Truett', 'dtruett7@constantcontact.com', 'Male', 'PO Box 99126', 2, 'Karanggintung');
insert into etudiants ( nom, prenom, date_inscription, genre, adresse, id_filiere, ville) values ( 'Steffi', 'Drever', 'sdrever8@about.com', 'Female', 'Room 1066', 1, 'Kapunduk');
insert into etudiants ( nom, prenom, date_inscription, genre, adresse, id_filiere, ville) values ( 'Jerrome', 'Patley', 'jpatley9@addtoany.com', 'Male', 'Room 207', 2, 'Laifang');

create table profs (
	id INT primary key auto_increment,
	name VARCHAR(255),
	matiere VARCHAR(50)
);
insert into profs ( name, matiere) values ( 'Mickie Tomasian', 'DEV');
insert into profs ( name, matiere) values ( 'Erinn Labat', 'DEV');
insert into profs ( name, matiere) values ( 'Willie Colaton', 'SVT');
insert into profs ( name, matiere) values ( 'Toddie Martensen', 'FRENCH');
insert into profs ( name, matiere) values ( 'Timmi Mowle', 'FRENCH');
insert into profs ( name, matiere) values ( 'Cirillo Zavattiero', 'MATH');
insert into profs ( name, matiere) values ( 'Nessy Hauxby', 'DEV');
insert into profs ( name, matiere) values ( 'Starla Dayer', 'SVT');
insert into profs ( name, matiere) values ( 'Ddene Challicum', 'FRENCH');
insert into profs ( name, matiere) values ( 'Bearnard Heinsen', 'MATH');

create table filieres (
	id INT PRIMARY KEY AUTO_INCREMENT,
	code VARCHAR(255),
	titre VARCHAR(255)
);
insert into filieres ( code, titre) values ( 'DEV', 'dev informatique');
insert into filieres ( code, titre) values ( 'GEST', "gestion d'entreprise");

