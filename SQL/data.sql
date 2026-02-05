SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- ROLES
-- ----------------------------
INSERT INTO role (id, name) VALUES
(1,'ROLE_ADM'),
(2,'ROLE_DRIVER'),
(3,'ROLE_PASSENGER'),
(4,'ROLE_EMPLOYEE');

-- ----------------------------
-- VEHICLE BRANDS
-- ----------------------------
INSERT INTO vehicle_brand (id, brand) VALUES
(1,'Porsche'),
(2,'Peugeot'),
(3,'Hyundai'),
(4,'Nissan'),
(5,'Audi'),
(6,'BMW'),
(7,'Volkswagen'),
(8,'Tesla');

-- ----------------------------
-- USERS
-- ----------------------------
INSERT INTO user VALUES
(1,'John','Doe','JDoe','john.doe@test.com','Asdfg123!','012345678','1981-12-12 15:51:53',NULL,'2025-12-12 15:51:53',NULL,35,2.2),
(3,'Mary','Doe','MDoe','mary.doe@test.com','Asdfg123!','02345678','2000-12-12 15:56:25',NULL,'2025-12-12 15:56:25',NULL,20,0),
(5,'Jack','Doe','JaDoe55','jack.joe@test.com','Asdfg123!','03456789','1999-12-12 16:03:35',NULL,'2025-12-12 16:03:35',NULL,10,4.1),
(6,'Jean','Dupont','JeDu@95','user@example.com','$2y$13$hash','0612345678','1995-06-15 00:00:00',NULL,'2026-01-22 11:53:37',NULL,20,0),
(7,'Marie','Dupont-Laurie','M.Dupont','test@test.com','$2y$13$hash','00000000','1995-01-12 00:00:00',NULL,'2026-01-22 14:38:30',NULL,20,0),
(8,'Marie','Dupont-Laurie','M.Dupont2','teste@test.com','$2y$13$hash','00000000','1976-01-21 00:00:00',NULL,'2026-01-22 16:10:03',NULL,20,0),
(9,'Marie','Dupont-Laurie','M.Dupont3','test2@test.com','$2y$13$hash','00000000','1986-01-24 00:00:00',NULL,'2026-01-22 16:45:18',NULL,20,0),

-- DEMO USERS
(100,'Admin','EcoRide','AdminUser','admin@ecoride.test','$2y$13$CSp6sJQTiK.eIWoID88t4.cIXiq/JL14kq/SlV1MltwZk9P6vK6yW','0600000001','1980-01-01 00:00:00',NULL,NOW(),NULL,100,5),
(101,'Emma','Employee','EmployeeUser','employee@ecoride.test','$2y$13$CSp6sJQTiK.eIWoID88t4.cIXiq/JL14kq/SlV1MltwZk9P6vK6yW','0600000002','1990-01-01 00:00:00',NULL,NOW(),NULL,50,4.5),
(102,'David','Driver','DriverUser','driver@ecoride.test','$2y$13$CSp6sJQTiK.eIWoID88t4.cIXiq/JL14kq/SlV1MltwZk9P6vK6yW','0600000003','1992-01-01 00:00:00',NULL,NOW(),NULL,30,4.8),
(103,'Paul','Passenger','PassengerUser','passenger@ecoride.test','$2y$13$CSp6sJQTiK.eIWoID88t4.cIXiq/JL14kq/SlV1MltwZk9P6vK6yW','0600000004','1998-01-01 00:00:00',NULL,NOW(),NULL,20,4),
(104,'Claire','Hybrid','HybridUser','hybrid@ecoride.test','$2y$13$CSp6sJQTiK.eIWoID88t4.cIXiq/JL14kq/SlV1MltwZk9P6vK6yW','0600000005','1995-01-01 00:00:00',NULL,NOW(),NULL,40,4.6);

-- ----------------------------
-- USER ADDRESS
-- ----------------------------
INSERT INTO user_address VALUES
(1,'10','Rue de Paris',NULL,'Paris','75000','France',NOW(),NULL),
(3,'25','Rue Victor Hugo',NULL,'Lyon','69000','France',NOW(),NULL),
(5,'18','Avenue Jean Jaurès',NULL,'Marseille','13000','France',NOW(),NULL),
(6,'3','Rue Nationale',NULL,'Lille','59000','France',NOW(),NULL),
(7,'40','Boulevard Gambetta',NULL,'Nice','06000','France',NOW(),NULL);

-- ----------------------------
-- USER ROLE
-- ----------------------------
INSERT INTO user_role VALUES
(1,1),(1,2),
(3,3),
(5,2),
(6,3),
(7,3),
(8,3),
(9,3),

-- DEMO ROLES
(100,1),
(101,4),
(102,2),
(103,3),
(104,2),
(104,3);

-- ----------------------------
-- VEHICLES
-- ----------------------------
INSERT INTO vehicle VALUES
(1,5,'Q4','Noir','AA-123-AA','2020-12-12 16:11:14',1,'2025-12-12 16:11:14',NULL,0),
(2,8,'Model 3','Blanc','BB-456-BB','2017-12-12 16:15:23',1,'2025-12-12 16:15:23',NULL,0),
(3,6,'X1','Bleu foncé','CC-789-CC','2025-12-12 16:18:26',0,'2025-12-12 16:18:26',NULL,1),

-- DEMO VEHICLE DRIVER
(100,7,'Golf','Gris','DD-111-DD','2021-05-05 10:00:00',1,'2025-12-12 10:00:00',NULL,0);

-- ----------------------------
-- USER VEHICLE
-- ----------------------------
INSERT INTO user_vehicle VALUES
(5,1),(5,2),(1,3),
(102,100);

-- ----------------------------
-- RIDE STATUS
-- ----------------------------
INSERT INTO ride_status VALUES
(1,'PENDING'),
(2,'CONFIRMED'),
(3,'AWAITING_PICKUP'),
(4,'IN_PROGRESS'),
(5,'COMPLETED'),
(6,'CANCELLED'),
(7,'NO_SHOW'),
(8,'DRIVER_NO_SHOW');

-- ----------------------------
-- RIDES
-- ----------------------------
INSERT INTO ride VALUES
(3,5,1,1,'Paris','7, Pl. Adolphe Chérioux','2026-02-20 16:58:44','14:00:00',NULL,'Lyon','Gare Part Dieu','2026-02-20 16:58:44','16:30:00',NULL,3,3,27,0,0,'Merci de ne pas manger dans la voiture','2025-12-12 16:58:44',NULL,NULL,150),

(4,5,3,3,'Paris','7, Pl. Adolphe Chérioux','2026-02-20 16:58:44','18:00:00',NULL,'Lyon','Gare Part Dieu','2026-02-20 16:58:44','21:00:00',NULL,2,1,25.5,1,0,NULL,'2025-12-12 16:58:45',NULL,NULL,180),

(5,1,1,2,'Paris','39, rue Gabriel Lamé','2026-02-20 17:14:21','10:00:00',NULL,'Lyon','Faculté de Médicine Lyon Est','2026-02-20 17:14:21','13:30:00',NULL,2,2,26.5,1,1,NULL,'2025-12-12 17:14:21',NULL,NULL,210),

-- DEMO RIDE
(100,102,100,2,'Nice','Gare de Nice','2026-03-01 09:00:00','09:00:00',NULL,'Marseille','Gare St Charles','2026-03-01 12:00:00','12:00:00',NULL,3,3,30,0,0,'Trajet demo','2026-02-01 00:00:00',NULL,NULL,120);

-- ----------------------------
-- RIDE PASSENGERS
-- ----------------------------
INSERT INTO ride_passenger VALUES
(3,3),(4,6),(5,7),
(100,103);

-- ----------------------------
-- EVALUATION STATUS
-- ----------------------------
INSERT INTO evaluation_status VALUES
(1,'EVAL_CREATED'),
(2,'EVAL_AWAITING_PASS'),
(3,'EVAL_SUBMITTED'),
(4,'EVAL_UNDER_REVIEW'),
(5,'PAYMENT_APPROVED'),
(6,'PAYMENT_DENIED'),
(7,'PAYMENT_BLOCKED');

-- ----------------------------
-- EVALUATION
-- ----------------------------
INSERT INTO evaluation VALUES
(1,3,3,3,NULL,1,5,'Trajet très agréable',NOW(),NULL,NULL,NULL),
(2,100,103,3,NULL,3,5,'Super trajet démo',NOW(),NULL,NULL,NULL);

SET FOREIGN_KEY_CHECKS=1;