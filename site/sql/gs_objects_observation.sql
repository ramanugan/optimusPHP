CREATE TABLE `gs_object_observations` (
  `id` double NOT NULL AUTO_INCREMENT,
  `imei` varchar(100) NOT NULL,
  `fecha_alta` datetime DEFAULT NULL,
  `observacion` mediumtext,
  `fecha_creacion` datetime DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_creador` varchar(100) DEFAULT NULL,
  `usuario_modificacion` varchar(100) DEFAULT NULL,
  `renta` double DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 27 DEFAULT CHARSET = latin1 

CREATE TABLE `gs_objects_reports` (
  `id` double NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `imei` varchar(100) NOT NULL,
  `sim_number` varchar(100) DEFAULT NULL,
  `protocol` varchar(100) DEFAULT NULL,
  `last_connection` datetime DEFAULT NULL,
  `contador` int(11) NOT NULL,
  `seguimiento` datetime DEFAULT NULL,
  `usuario` varchar(100) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;

CREATE TABLE `gs_objects` (
  `imei` varchar(20) COLLATE utf8_bin NOT NULL,
  `protocol` varchar(50) COLLATE utf8_bin NOT NULL,
  `traccar_id` int(11) NOT NULL,
  `positionid` int(11) NOT NULL,
  `net_protocol` varchar(3) COLLATE utf8_bin NOT NULL,
  `ip` varchar(50) COLLATE utf8_bin NOT NULL,
  `port` varchar(10) COLLATE utf8_bin NOT NULL,
  `active` varchar(5) COLLATE utf8_bin NOT NULL,
  `object_expire` varchar(5) COLLATE utf8_bin NOT NULL,
  `object_expire_dt` date NOT NULL,
  `manager_id` int(11) NOT NULL,
  `dt_server` datetime NOT NULL,
  `dt_tracker` datetime NOT NULL,
  `lat` double NOT NULL,
  `lng` double NOT NULL,
  `altitude` double NOT NULL,
  `angle` double NOT NULL,
  `speed` double NOT NULL,
  `loc_valid` int(11) NOT NULL,
  `params` varchar(2048) COLLATE utf8_bin NOT NULL,
  `dt_last_stop` datetime NOT NULL,
  `dt_last_idle` datetime NOT NULL,
  `dt_last_move` datetime NOT NULL,
  `name` varchar(50) COLLATE utf8_bin NOT NULL,
  `icon` varchar(256) COLLATE utf8_bin NOT NULL,
  `map_arrows` varchar(512) COLLATE utf8_bin NOT NULL,
  `map_icon` varchar(5) COLLATE utf8_bin NOT NULL,
  `tail_color` varchar(7) COLLATE utf8_bin NOT NULL,
  `tail_points` int(11) NOT NULL,
  `device` varchar(30) COLLATE utf8_bin NOT NULL,
  `sim_number` varchar(50) COLLATE utf8_bin NOT NULL,
  `model` varchar(50) COLLATE utf8_bin NOT NULL,
  `vin` varchar(50) COLLATE utf8_bin NOT NULL,
  `plate_number` varchar(50) COLLATE utf8_bin NOT NULL,
  `odometer_type` varchar(10) COLLATE utf8_bin NOT NULL,
  `engine_hours_type` varchar(10) COLLATE utf8_bin NOT NULL,
  `odometer` double NOT NULL,
  `engine_hours` int(11) NOT NULL,
  `fcr` varchar(512) COLLATE utf8_bin NOT NULL,
  `time_adj` varchar(30) COLLATE utf8_bin NOT NULL,
  `accuracy` varchar(1024) COLLATE utf8_bin NOT NULL,
  `accvirt` varchar(5) COLLATE utf8_bin NOT NULL,
  `accvirt_cn` varchar(128) COLLATE utf8_bin NOT NULL,
  `dt_chat` datetime NOT NULL,
  `mileage_1` double NOT NULL,
  `mileage_2` double NOT NULL,
  `mileage_3` double NOT NULL,
  `mileage_4` double NOT NULL,
  `mileage_5` double NOT NULL,
  `dt_mileage` datetime NOT NULL,
  `last_img_file` varchar(50) COLLATE utf8_bin NOT NULL,
  `no_sensor1` varchar(50) COLLATE utf8_bin NOT NULL,
  `no_sensor2` varchar(50) COLLATE utf8_bin NOT NULL,
  `cuenta_padre` varchar(50) COLLATE utf8_bin NOT NULL,
  `contador` int(11) NOT NULL,
  `seguimiento` enum('false', 'true') COLLATE utf8_bin NOT NULL DEFAULT 'false',
  PRIMARY KEY (`imei`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_bin;

parcial2-datos-clientes

ALTER TABLE `gs_users`
ADD COLUMN `name` varchar(100) COLLATE utf8_bin NOT NULL AFTER `email`,
ADD COLUMN `company` varchar(100) COLLATE utf8_bin NOT NULL AFTER `name`,
ADD COLUMN `address` varchar(100) COLLATE utf8_bin NOT NULL AFTER `company`,
ADD COLUMN `code` varchar(10) COLLATE utf8_bin NOT NULL AFTER `address`,
ADD COLUMN `city` varchar(50) COLLATE utf8_bin NOT NULL AFTER `code`,
ADD COLUMN `country` varchar(50) COLLATE utf8_bin NOT NULL AFTER `city`,
ADD COLUMN `phone1` varchar(20) COLLATE utf8_bin NOT NULL AFTER `country`,
ADD COLUMN `phone2` varchar(20) COLLATE utf8_bin NOT NULL AFTER `phone1`,
ADD COLUMN `email1` varchar(100) COLLATE utf8_bin NOT NULL AFTER `phone2`,
ADD COLUMN `email2` varchar(100) COLLATE utf8_bin NOT NULL AFTER `email1`;


/ / para viajes programados 

DROP TABLE app.gs_object_tasks;

CREATE TABLE `gs_object_tasks` (
  `task_id` int NOT NULL AUTO_INCREMENT,
  `delivered` int NOT NULL,
  `dt_task` datetime NOT NULL,
  `journey_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `driver_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `imei_truck_tractor` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `imei_trailer_1` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `dolly` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `imei_trailer_2` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `desc` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `priority` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `status` int NOT NULL,
  `initial_zone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `ended_zone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `start_from_dt` datetime NOT NULL,
  `start_to_dt` datetime NOT NULL,
  `end_from_dt` datetime NOT NULL,
  `end_to_dt` datetime NOT NULL,
  `carta_porte` varchar(100) COLLATE utf8mb3_bin DEFAULT NULL,
  PRIMARY KEY (`task_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

// campo para la carta porte
ALTER TABLE gpsdb.gs_object_tasks ADD carta_porte varchar(100) NULL;

// campo para # de cliente
ALTER TABLE gpsdb.gs_user_objects ADD client_id INT NOT NULL;

// mas documentos de tareas programadas
ALTER TABLE gpsdb.gs_object_tasks ADD doc1 varchar(100) NULL;
ALTER TABLE gpsdb.gs_object_tasks ADD doc2 varchar(100) NULL;
ALTER TABLE gpsdb.gs_object_tasks ADD doc3 varchar(100) NULL;


//documentos
CREATE TABLE `gs_user_docs` (
  `user_id` int NOT NULL,
  `doc_1` varchar(255) DEFAULT '',
  `doc_2` varchar(255) DEFAULT '',
  `doc_3` varchar(255) DEFAULT '',
  `doc_4` varchar(255) DEFAULT '',
  `doc_5` varchar(255) DEFAULT '',
  `doc_6` varchar(255) DEFAULT '',
  `doc_7` varchar(255) DEFAULT '',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB;

// Planes de venta
ALTER TABLE gs_objects
ADD COLUMN plan VARCHAR(20) AFTER port,
ADD COLUMN fecha_mtto VARCHAR(20) AFTER plan,
ADD COLUMN fecha_garantia VARCHAR(20) AFTER fecha_mtto;

ALTER TABLE gs_object_services
ADD COLUMN plan VARCHAR(20) AFTER imei;

			ALTER TABLE gs_user_events 
  ADD COLUMN markers VARCHAR(20) AFTER zones;


  
  
//Tablas para CRM

DROP TABLE `gs_object_data_details`;

DROP TABLE `gs_object_data`;

CREATE TABLE `gs_object_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `imei` varchar(100) NOT NULL,
  `fecha_` datetime NOT NULL,
  `fecha_estado` datetime NOT NULL,
  `fecha_servicio` datetime NOT NULL,
  `event` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `user_id_` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `contador` varchar(100) NOT NULL,
  `email_user` varchar(1000) NOT NULL,
  `email_client` varchar(1000) NOT NULL,
  `attended` enum('Sin atender','En progreso','Atendido','Agendado') CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT 'Sin atender',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;




CREATE TABLE `gs_object_data_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `imei` varchar(100) NOT NULL,
  `details` tinytext,
  `comment` varchar(1000) NOT NULL,
  `event` varchar(100) NOT NULL,
  `fecha` datetime NOT NULL,
  `user_id` int NOT NULL,
  `attended_status` enum('Sin atender','En progreso','Atendido','Agendado') CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT 'Sin atender',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



ALTER TABLE app.gs_objects ADD no_sensor3 varchar(100) NOT NULL;
ALTER TABLE app.gs_objects CHANGE no_sensor3 no_sensor3 varchar(100) NOT NULL AFTER no_sensor2;

ALTER TABLE app.gs_objects ADD sensor_trademark varchar(100) NOT NULL;
ALTER TABLE app.gs_objects CHANGE sensor_trademark sensor_trademark varchar(100) NOT NULL AFTER last_img_file;


//Agrega tablas para driver_name


ALTER TABLE gs_user_object_drivers
ADD COLUMN licence_img_file VARCHAR(255) BEFORE driver_img_file,
ADD COLUMN id_img_file VARCHAR(255) AFTER licence_img_file,
ADD COLUMN nss_img_file VARCHAR(255) AFTER id_img_file,
ADD COLUMN otro_img_file VARCHAR(255) AFTER nss_img_file;



ALTER TABLE gs_user_object_drivers
ADD COLUMN driver_rfc VARCHAR(255) NOT NULL AFTER driver_name,
ADD COLUMN driver_nss VARCHAR(255) NOT NULL AFTER driver_rfc,
ADD COLUMN driver_licence VARCHAR(255) NOT NULL AFTER driver_nss,
ADD COLUMN driver_licence_date DATE NOT NULL DEFAULT '2025-01-01' AFTER driver_licence,
ADD COLUMN driver_ine VARCHAR(255) NOT NULL AFTER driver_licence_date,
ADD COLUMN driver_ine_date DATE NOT NULL DEFAULT '2025-01-01' AFTER driver_ine,
ADD COLUMN driver_curp VARCHAR(255) NOT NULL AFTER driver_ine_date;


//agrega grupo a campos perso
ALTER TABLE gpsdb.gs_object_custom_fields ADD group_id VARCHAR(10) NOT NULL;
ALTER TABLE gpsdb.gs_object_custom_fields CHANGE group_id group_id VARCHAR(10) NOT NULL AFTER field_id;


// WS_PARAMS
ALTER TABLE gs_user_object_groups ADD ws_name VARCHAR(100) NOT NULL;
ALTER TABLE gs_user_object_groups ADD ws_pass VARCHAR(100) NOT NULL;


// add device attributes
ALTER TABLE gpsdb.gs_objects ADD supplier varchar(524) DEFAULT '' NULL;
ALTER TABLE gpsdb.gs_objects ADD rent_cost_device DOUBLE DEFAULT 0 NULL;
ALTER TABLE gpsdb.gs_objects ADD dt_purchase_device DATETIME NULL;
ALTER TABLE gpsdb.gs_objects MODIFY COLUMN dt_income_device datetime DEFAULT NOW() NULL;
ALTER TABLE gpsdb.gs_objects ADD dt_closing_date DATETIME NULL;


//yabla para datos del vehiculo
CREATE TABLE `gs_object_vehicle_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `imei` varchar(20) NOT NULL,
  `year` int DEFAULT NULL,
  `brand` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `model_` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `plate` varchar(20) DEFAULT NULL,
  `vin` varchar(100) DEFAULT NULL,
  `odometer` bigint DEFAULT '0',
  `insurance` varchar(100) DEFAULT NULL,
  `insurance_exp` varchar(50) DEFAULT NULL,
  `fuel` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `imei_unique` (`imei`)
) ENGINE=InnoDB AUTO_INCREMENT=4097 DEFAULT CHARSET=utf8mb3;
