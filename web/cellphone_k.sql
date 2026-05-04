-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: localhost    Database: cellphone_k
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_inf`
--

DROP TABLE IF EXISTS `admin_inf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_inf` (
  `MaAdmin` int NOT NULL AUTO_INCREMENT,
  `TenDangNhap` varchar(50) DEFAULT NULL,
  `MatKhau` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `HoTen` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`MaAdmin`),
  UNIQUE KEY `TenDangNhap` (`TenDangNhap`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_inf`
--

LOCK TABLES `admin_inf` WRITE;
/*!40000 ALTER TABLE `admin_inf` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_inf` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chitiethoadon`
--

DROP TABLE IF EXISTS `chitiethoadon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chitiethoadon` (
  `MaCTHD` int NOT NULL AUTO_INCREMENT,
  `MaHoaDon` int DEFAULT NULL,
  `MaSanPham` int DEFAULT NULL,
  `TenMau` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `KichThuoc` varchar(30) DEFAULT NULL,
  `SoLuong` int DEFAULT NULL,
  `ThanhTien` decimal(18,2) DEFAULT NULL,
  PRIMARY KEY (`MaCTHD`),
  KEY `MaHoaDon` (`MaHoaDon`),
  KEY `MaSanPham` (`MaSanPham`),
  CONSTRAINT `chitiethoadon_ibfk_1` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadon` (`MaHoaDon`) ON DELETE CASCADE,
  CONSTRAINT `chitiethoadon_ibfk_2` FOREIGN KEY (`MaSanPham`) REFERENCES `sanpham` (`MaSanPham`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chitiethoadon`
--

LOCK TABLES `chitiethoadon` WRITE;
/*!40000 ALTER TABLE `chitiethoadon` DISABLE KEYS */;
INSERT INTO `chitiethoadon` VALUES (1,1,36,'Xanh','4GB/64GB',2,5800000.00);
/*!40000 ALTER TABLE `chitiethoadon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chitietsanpham`
--

DROP TABLE IF EXISTS `chitietsanpham`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chitietsanpham` (
  `MaSanPham` int NOT NULL,
  `KichThuocManHinh` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `CongNgheManHinh` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `DoPhanGiaiManHinh` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `TinhNangManHinh` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `CameraSau` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `QuayVideoSau` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `CameraTruoc` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `QuayVideoTruoc` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ChipSet` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Pin` varchar(100) DEFAULT NULL,
  `CongNgheSac` varchar(100) DEFAULT NULL,
  `TheSim` varchar(100) DEFAULT NULL,
  `HeDieuHanh` varchar(100) DEFAULT NULL,
  `HoTroMang` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Wifi` varchar(100) DEFAULT NULL,
  `Bluetooth` varchar(100) DEFAULT NULL,
  `Gps` varchar(100) DEFAULT NULL,
  `KhangNuocBui` varchar(100) DEFAULT NULL,
  `CongNgheAmThanh` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`MaSanPham`),
  CONSTRAINT `chitietsanpham_ibfk_1` FOREIGN KEY (`MaSanPham`) REFERENCES `sanpham` (`MaSanPham`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chitietsanpham`
--

LOCK TABLES `chitietsanpham` WRITE;
/*!40000 ALTER TABLE `chitietsanpham` DISABLE KEYS */;
INSERT INTO `chitietsanpham` VALUES (1,'6.9 inches','Dynamic AMOLED 2X','1440 x 3200 pixels','HDR10+, 120Hz','108 MP (Wide), 12 MP (Periscope Telephoto), 12 MP (Ultra-wide)','4K@30/60/120fps, 1080p@30/60/240fps, 720p@960fps, HDR10+','40 MP (Wide)','4K@30fps, 1080p@30fps','Exynos 2200 (5 nm)','5000 mAh','Fast charging 100W','Dual SIM (Nano-SIM, dual stand-by)','Android 14','5G/4G LTE','Wi-Fi 802.11 a/b/g/n/ac/6e','Bluetooth 5.3','A-GPS, GLONASS, GALILEO, BDS','IP68 dust/water resistant','Stereo speakers tuned by AKG'),(2,'6.8 inches','Dynamic AMOLED 2X','1440 x 3088 pixels','HDR10+, 120Hz','108 MP (Wide), 10 MP (Periscope Telephoto), 10 MP (Telephoto), 12 MP (Ultra-wide)','8K@24fps, 4K@30/60fps, 1080p@30/60/240fps, 720p@960fps, HDR10+','40 MP (Wide)','4K@30/60fps, 1080p@30fps','Exynos 2100 (5 nm)','5000 mAh','Fast charging 45W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, One UI 4.1','5G/4G LTE','Wi-Fi 802.11 a/b/g/n/ac/6e','Bluetooth 5.2','A-GPS, GLONASS, BDS, GALILEO','IP68 dust/water resistant','Stereo speakers tuned by AKG'),(3,'6.9 inches','Dynamic AMOLED 2X','1440 x 3200 pixels','HDR10+, 120Hz','200 MP (Wide), 10 MP (Periscope Telephoto), 10 MP (Telephoto), 12 MP (Ultra-wide)','8K@30fps, 4K@30/60fps, 1080p@30/60/240fps, 720p@960fps, HDR10+','40 MP (Wide)','4K@30/60fps, 1080p@30fps','Exynos 2200 (5 nm)','5000 mAh','Fast charging 100W','Dual SIM (Nano-SIM, dual stand-by)','Android 13, One UI 5.1','5G/4G LTE','Wi-Fi 802.11 a/b/g/n/ac/6e','Bluetooth 5.3','A-GPS, GLONASS, BDS, GALILEO','IP68 dust/water resistant','Stereo speakers tuned by AKG'),(4,'7.1 inches (Main), 6.23 inches (Cover)','Dynamic AMOLED 2X','1768 x 2208 pixels (Main), 816 x 2260 pixels (Cover)','HDR10+, 120Hz','108 MP (Wide), 12 MP (Ultra-wide), 12 MP (Telephoto)','4K@30/60/120fps, 1080p@30/60/240fps, 720p@960fps, HDR10+','10 MP (Wide)','4K@30fps, 1080p@30/60/240fps (Cover)','Exynos 2200 (5 nm)','4400 mAh','Fast charging 100W, Wireless charging 50W, Reverse wireless charging 10W','eSIM','Android 14, One UI 5.1','5G/4G LTE','Wi-Fi 802.11 a/b/g/n/ac/6e','Bluetooth 5.3','A-GPS, GLONASS, GALILEO, BDS','IPX8 water resistant (up to 1.5m for 30 mins)','Stereo speakers tuned by AKG'),(5,'6.5 inches','Super AMOLED','1080 x 2400 pixels','HDR10+','64 MP (Wide), 12 MP (Ultra-wide), 5 MP (Macro), 5 MP (Depth)','4K@30fps, 1080p@30/120fps','32 MP (Wide)','1080p@30fps','Exynos 9611 (10 nm)','5000 mAh','Fast charging 25W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, One UI 4.1','4G LTE','Wi-Fi 802.11 a/b/g/n/ac','Bluetooth 5.0','GPS, GLONASS, GALILEO, BDS','IP67 dust/water resistant (up to 1m for 30 mins)','Stereo speakers'),(6,'6.4 inches','Super AMOLED','1080 x 2400 pixels','HDR10+','48 MP (Wide), 8 MP (Ultra-wide), 5 MP (Macro)','4K@30fps, 1080p@30/60fps','20 MP (Wide)','1080p@30fps','Exynos 1280 (5 nm)','5000 mAh','Fast charging 25W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, One UI 4.1','5G','Wi-Fi 802.11 a/b/g/n/ac','Bluetooth 5.1','GPS, GLONASS, GALILEO, BDS','IP67 dust/water resistant (up to 1m for 30 mins)','Stereo speakers'),(7,'6.7 inches','Super AMOLED','1080 x 2400 pixels','HDR10+','108 MP (Wide), 12 MP (Ultra-wide), 5 MP (Macro), 5 MP (Depth)','4K@30/60fps, 1080p@30/120fps','32 MP (Wide)','1080p@30fps','Exynos 1380 (5 nm)','6000 mAh','Fast charging 25W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, One UI 4.1','5G','Wi-Fi 802.11 a/b/g/n/ac','Bluetooth 5.2','GPS, GLONASS, GALILEO, BDS','IP68 dust/water resistant (up to 1.5m for 30 mins)','Stereo speakers'),(8,'6.5 inches','PLS LCD','720 x 1600 pixels','None','50 MP (Wide), 2 MP (Depth)','1080p@30fps','5 MP','720p@30fps','Exynos 850 (8 nm)','5000 mAh','Fast charging 15W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, One UI 4.1','4G','Wi-Fi 802.11 a/b/g/n/ac','Bluetooth 5.0','GPS, GLONASS, GALILEO, BDS','None','Mono speaker'),(9,'6.5 inches','Super AMOLED','1080 x 2400 pixels','120Hz, HDR10+','64 MP (Wide), 12 MP (Ultra Wide), 5 MP (Macro), 5 MP (Depth)','4K@30/60fps, 1080p@30/60/240fps','32 MP','4K@30fps, 1080p@30/60fps','Exynos 1280 (5 nm)','5000 mAh','Fast charging 25W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, One UI 4.1','5G','Wi-Fi 802.11 a/b/g/n/ac/6','Bluetooth 5.1','GPS, GLONASS, GALILEO, BDS','IP67 dust/water resistant','Stereo speakers'),(10,'6.6 inches','PLS LCD','1080 x 2408 pixels','None','50 MP (Wide), 5 MP (Ultra Wide), 2 MP (Macro)','1080p@30fps','8 MP','1080p@30fps','Exynos 1080 (5 nm)','5000 mAh','Fast charging 15W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, One UI 4.1','5G','Wi-Fi 802.11 a/b/g/n/ac','Bluetooth 5.0','GPS, GLONASS, GALILEO, BDS','None','Mono speaker'),(11,'6.67 inches','AMOLED','1080 x 2400 pixels','HDR10+','108 MP (Wide), 8 MP (Ultra Wide), 5 MP (Macro)','4K@30fps, 1080p@30/60fps','16 MP','1080p@30fps','Snapdragon 732G (8 nm)','5020 mAh','Fast charging 33W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, MIUI 13','5G','Wi-Fi 802.11 a/b/g/n/ac/6','Bluetooth 5.1','GPS, GLONASS, GALILEO, BDS','IP53','Stereo speakers'),(12,'6.67 inches','AMOLED','1080 x 2400 pixels','HDR10+','64 MP (Wide), 8 MP (Ultra Wide), 2 MP (Macro)','4K@30fps, 1080p@30/60fps','16 MP','1080p@30fps','Snapdragon 695 5G (6 nm)','5000 mAh','Fast charging 33W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, MIUI 13','5G','Wi-Fi 802.11 a/b/g/n/ac','Bluetooth 5.1','GPS, GLONASS, GALILEO, BDS','IP53','Stereo speakers'),(13,'6.43 inches','AMOLED','1080 x 2400 pixels','None','48 MP (Wide), 2 MP (Macro)','1080p@30fps','13 MP','1080p@30fps','Mediatek Helio G95 (12 nm)','5000 mAh','Fast charging 33W','Dual SIM (Nano-SIM, dual stand-by)','Android 11, MIUI 12.5','4G','Wi-Fi 802.11 a/b/g/n/ac','Bluetooth 5.0','GPS, GLONASS, GALILEO, BDS','None','Mono speaker'),(14,'6.67 inches','Super AMOLED','1080 x 2400 pixels','HDR10+','108 MP (Wide), 13 MP (Ultra Wide), 5 MP (Macro)','4K@30/60fps, 1080p@30/60/120/240/960fps, gyro-EIS','32 MP','1080p@30fps','Snapdragon 870 (7 nm)','5000 mAh','Fast charging 120W, 100% in 23 min (advertised)','Dual SIM (Nano-SIM, dual stand-by)','Android 12, MIUI 13','5G','Wi-Fi 802.11 a/b/g/n/ac/6e','Bluetooth 5.2','GPS, GLONASS, GALILEO, BDS','IP53','Stereo speakers'),(15,'6.78 inches','AMOLED','1080 x 2400 pixels','HDR10+','108 MP (Wide), 8 MP (Periscope telephoto), 13 MP (Ultra Wide)','8K@30fps, 4K@30/60/120fps, 1080p@30/60/240/960fps, gyro-EIS, HDR10+','32 MP','1080p@30fps','Snapdragon 8 Gen 1 (4 nm)','4800 mAh','Fast charging 120W, 100% in 21 min (advertised)','Dual SIM (Nano-SIM, dual stand-by)','Android 12, MIUI 13','5G','Wi-Fi 802.11 a/b/g/n/ac/6e','Bluetooth 5.2','GPS, GLONASS, GALILEO, BDS','IP53','Stereo speakers'),(16,'6.81 inches','AMOLED','1440 x 3200 pixels','HDR10+','108 MP (Wide), 8 MP (Periscope telephoto), 13 MP (Ultra Wide)','8K@30fps, 4K@30/60/120fps, 1080p@30/60/240/960fps, gyro-EIS, HDR10+','32 MP','1080p@30fps','Snapdragon 8 Gen 1 (4 nm)','4800 mAh','Fast charging 120W, 100% in 21 min (advertised)','Dual SIM (Nano-SIM, dual stand-by)','Android 12, MIUI 13','5G','Wi-Fi 802.11 a/b/g/n/ac/6e','Bluetooth 5.2','GPS, GLONASS, GALILEO, BDS','IP53','Stereo speakers'),(17,'6.58 inches','IPS LCD','1080 x 2400 pixels','None','50 MP (Wide), 2 MP (Macro)','1080p@30fps','16 MP','1080p@30fps','Snapdragon 680 (6 nm)','5000 mAh','Fast charging 33W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, MIUI 13','4G','Wi-Fi 802.11 a/b/g/n/ac','Bluetooth 5.2','GPS, GLONASS, GALILEO, BDS','None','Mono speaker'),(18,'6.53 inches','IPS LCD','1080 x 2400 pixels','None','50 MP (Wide), 2 MP (Macro)','1080p@30fps','16 MP','1080p@30fps','Mediatek Helio G80 (12 nm)','5000 mAh','Fast charging 18W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, MIUI 13','4G','Wi-Fi 802.11 a/b/g/n/ac','Bluetooth 5.1','GPS, GLONASS, GALILEO, BDS','None','Mono speaker'),(19,'6.01 inches','Super AMOLED','720 x 1560 pixels','None','48 MP (Wide), 8 MP (Ultrawide), 2 MP (Depth)','2160p@30fps','32 MP','1080p@30fps','Qualcomm Snapdragon 665','4030 mAh','Fast charging 18W','Dual SIM (Nano-SIM, dual stand-by)','Android 10, MIUI 11','4G','Wi-Fi 802.11 a/b/g/n/ac','Bluetooth 5.0','GPS, GLONASS, GALILEO, BDS','None','Loudspeaker'),(20,'6.6 inches','IPS LCD','1080 x 2400 pixels','None','108 MP (Wide), 8 MP (Ultrawide), 2 MP (Macro), 2 MP (Depth)','4K@30fps, 1080p@30/60/120/240fps, 1080p@960fps; gyro-EIS','32 MP','1080p@30fps','Qualcomm Snapdragon 695','5000 mAh','Fast charging 67W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, MIUI 13','5G','Wi-Fi 802.11 a/b/g/n/ac/6, dual-band, Wi-Fi Direct, hotspot','Bluetooth 5.2','GPS, GLONASS, GALILEO, BDS, QZSS','IP53, dust and splash protection','Stereo speakers'),(21,'6.8 inches','AMOLED','1080 x 2520 pixels','HDR10+','50 MP (Wide), 8 MP (Ultrawide), 2 MP (Macro)','4K@30/60fps, 1080p@30/60/120fps; gyro-EIS','32 MP','1080p@30fps','MediaTek Dimensity 9000+','4300 mAh','Fast charging 44W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, ColorOS 12','5G','Wi-Fi 802.11 a/b/g/n/ac/6, dual-band, Wi-Fi Direct, hotspot','Bluetooth 5.3','GPS, GLONASS, GALILEO, BDS, QZSS','IP54, dust and splash resistant','Stereo speakers'),(22,'6.7 inches','AMOLED','1440 x 3216 pixels','HDR10+','50 MP (Wide), 50 MP (Ultrawide), 13 MP (Telephoto)','4K@30/60fps, 1080p@30/60/120fps; gyro-EIS','32 MP','1080p@30fps','Qualcomm Snapdragon 8 Gen 1','5000 mAh','Fast charging 80W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, ColorOS 12','5G','Wi-Fi 802.11 a/b/g/n/ac/6, dual-band, Wi-Fi Direct, hotspot','Bluetooth 5.2','GPS, GLONASS, GALILEO, BDS, QZSS','IP68, dust and water resistant','Stereo speakers'),(23,'6.55 inches','AMOLED','1080 x 2400 pixels','HDR10+','50 MP (Wide), 8 MP (Ultrawide), 2 MP (Macro)','4K@30fps, 1080p@30/60/120fps','32 MP','1080p@30fps','MediaTek Dimensity 1200','4500 mAh','Fast charging 65W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, ColorOS 12','5G','Wi-Fi 802.11 a/b/g/n/ac/6, dual-band, Wi-Fi Direct, hotspot','Bluetooth 5.2','GPS, GLONASS, GALILEO, BDS, QZSS','No','Stereo speakers'),(24,'6.5 inches','AMOLED','1080 x 2400 pixels','HDR10+','64 MP (Wide), 8 MP (Ultrawide), 2 MP (Macro)','4K@30fps, 1080p@30/60/120fps','32 MP','1080p@30fps','Qualcomm Snapdragon 870','4500 mAh','Fast charging 65W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, ColorOS 12','5G','Wi-Fi 802.11 a/b/g/n/ac/6, dual-band, Wi-Fi Direct, hotspot','Bluetooth 5.2','GPS, GLONASS, GALILEO, BDS, QZSS','No','Stereo speakers'),(25,'6.5 inches','IPS LCD','720 x 1600 pixels','None','16 MP (Wide), 2 MP (Depth)','1080p@30fps','8 MP','1080p@30fps','MediaTek Helio G96','5000 mAh','Fast charging 18W','Dual SIM (Nano-SIM, dual stand-by)','Android 11, ColorOS 11.1','4G','Wi-Fi 802.11 a/b/g/n/ac, dual-band, Wi-Fi Direct, hotspot','Bluetooth 5.1','GPS, GLONASS, GALILEO, BDS','No','Stereo speakers'),(26,'6.7 inches','AMOLED','1080 x 2400 pixels','None','50 MP (Wide), 8 MP (Ultra-wide), 2 MP (Macro)','4K@30fps, 1080p@30/60/120fps; gyro-EIS, HDR','32 MP','1080p@30fps','MediaTek Dimensity 1200','4500 mAh','Fast charging 65W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, ColorOS 12','5G','Wi-Fi 802.11 a/b/g/n/ac/6, dual-band, Wi-Fi Direct, hotspot','Bluetooth 5.2','GPS, GLONASS, GALILEO, BDS, QZSS','No','Stereo speakers'),(27,'6.6 inches','AMOLED','1080 x 2400 pixels','None','64 MP (Wide), 8 MP (Ultra-wide), 2 MP (Macro), 2 MP (Depth)','4K@30fps, 1080p@30/60/120fps; gyro-EIS, HDR','32 MP','1080p@30fps','Qualcomm Snapdragon 778G','4500 mAh','Fast charging 65W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, ColorOS 12','4G','Wi-Fi 802.11 a/b/g/n/ac/6, dual-band, Wi-Fi Direct, hotspot','Bluetooth 5.2','GPS, GLONASS, GALILEO, BDS, QZSS','No','Stereo speakers'),(28,'6.4 inches','AMOLED','1080 x 2400 pixels','None','64 MP (Wide), 8 MP (Ultra-wide), 2 MP (Macro)','4K@30fps, 1080p@30/60/120fps; gyro-EIS, HDR','32 MP','1080p@30fps','Qualcomm Snapdragon 750G','4500 mAh','Fast charging 33W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, ColorOS 12','4G','Wi-Fi 802.11 a/b/g/n/ac/6, dual-band, Wi-Fi Direct, hotspot','Bluetooth 5.2','GPS, GLONASS, GALILEO, BDS, QZSS','No','Stereo speakers'),(29,'6.5 inches','IPS LCD','720 x 1600 pixels','None','48 MP (Wide), 2 MP (Macro), 2 MP (Depth)','1080p@30fps','8 MP','1080p@30fps','Qualcomm Snapdragon 680','5000 mAh','Fast charging 18W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, ColorOS 12','4G','Wi-Fi 802.11 a/b/g/n/ac, dual-band, Wi-Fi Direct, hotspot','Bluetooth 5.1','GPS, GLONASS, GALILEO, BDS','No','Stereo speakers'),(30,'6.3 inches','IPS LCD','720 x 1600 pixels','None','48 MP (Wide), 2 MP (Depth)','1080p@30fps','8 MP','1080p@30fps','MediaTek Helio P35','4500 mAh','Fast charging 18W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, ColorOS 12','4G','Wi-Fi 802.11 a/b/g/n/ac, dual-band, Wi-Fi Direct, hotspot','Bluetooth 5.1','GPS, GLONASS, GALILEO, BDS','No','Stereo speakers'),(31,'6.7 inches','AMOLED','1080 x 2400 pixels','HDR10','108 MP (Wide), 8 MP (Ultra-wide), 2 MP (Macro)','4K@30fps','16 MP','1080p@30fps','Helio G99','5000 mAh','Fast charging 65W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, Realme UI 3.0','5G','Wi-Fi 802.11 a/b/g/n/ac/6, dual-band, Wi-Fi Direct, hotspot','Bluetooth 5.2','GPS, GLONASS, GALILEO, BDS','Yes','Stereo speakers'),(32,'6.5 inches','IPS LCD','720 x 1600 pixels','None','50 MP (Wide), 2 MP (Macro)','1080p@30fps','8 MP','1080p@30fps','MediaTek Helio G85','5000 mAh','Fast charging 18W','Dual SIM (Nano-SIM, dual stand-by)','Android 11, Realme UI 2.0','4G','Wi-Fi 802.11 a/b/g/n/ac, dual-band, Wi-Fi Direct, hotspot','Bluetooth 5.0','GPS, GLONASS, GALILEO','No','Mono speaker'),(33,'6.6 inches','IPS LCD','1080 x 2408 pixels','90Hz','64 MP (Wide), 2 MP (Depth)','1080p@30fps','8 MP','1080p@30fps','MediaTek Helio G88','5000 mAh','Fast charging 33W','Dual SIM (Nano-SIM, dual stand-by)','Android 11, Realme UI 2.0','4G','Wi-Fi 802.11 a/b/g/n/ac, dual-band, Wi-Fi Direct, hotspot','Bluetooth 5.1','GPS, GLONASS, GALILEO','No','Mono speaker'),(34,'6.5 inches','IPS LCD','720 x 1600 pixels','None','50 MP (Wide), 2 MP (Macro)','1080p@30fps','8 MP','1080p@30fps','Unisoc T612','5000 mAh','Fast charging 18W','Dual SIM (Nano-SIM, dual stand-by)','Android 11, Realme UI Go','4G','Wi-Fi 802.11 a/b/g/n/ac, dual-band, Wi-Fi Direct, hotspot','Bluetooth 5.0','GPS, GLONASS, GALILEO','No','Mono speaker'),(35,'6.5 inches','IPS LCD','720 x 1600 pixels','None','8 MP (Wide)','1080p@30fps','5 MP','720p@30fps','Unisoc SC9863A','4000 mAh','Fast charging 10W','Dual SIM (Nano-SIM, dual stand-by)','Android 11, Realme UI Go','4G','Wi-Fi 802.11 b/g/n, hotspot','Bluetooth 4.2','GPS, GLONASS','No','Mono speaker'),(36,'6.5 inches','IPS LCD','720 x 1600 pixels','None','8 MP (Wide)','1080p@30fps','5 MP','720p@30fps','Unisoc SC9863A','5000 mAh','Fast charging 10W','Dual SIM (Nano-SIM, dual stand-by)','Android 11, Realme UI Go','4G','Wi-Fi 802.11 b/g/n, hotspot','Bluetooth 4.2','GPS, GLONASS','No','Mono speaker'),(37,'6.7 inches','IPS LCD','1080 x 2400 pixels','None','12 MP (Wide)','1080p@30fps','8 MP','1080p@30fps','Unisoc T612','6000 mAh','Fast charging 18W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, Realme UI 3.0','4G','Wi-Fi 802.11 a/b/g/n/ac, dual-band','Bluetooth 5.0','GPS, GLONASS, BDS','IP68','Stereo speakers'),(38,'6.7 inches','AMOLED','1080 x 2412 pixels','120Hz, HDR10+','108 MP (Wide), 8 MP (Ultrawide), 2 MP (Macro)','4K@30fps','32 MP','1080p@30fps','Dimensity 920','5000 mAh','Fast charging 67W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, Realme UI 3.0','5G','Wi-Fi 802.11 a/b/g/n/ac/6, dual-band','Bluetooth 5.2','GPS, GLONASS, BDS, GALILEO','IP53','Stereo speakers'),(39,'6.6 inches','IPS LCD','1080 x 2400 pixels','90Hz','50 MP (Wide), 2 MP (Macro), 2 MP (Depth)','1080p@30fps','16 MP','1080p@30fps','Snapdragon 695','5000 mAh','Fast charging 33W','Dual SIM (Nano-SIM, dual stand-by)','Android 12, Realme UI 3.0','5G','Wi-Fi 802.11 a/b/g/n/ac, dual-band','Bluetooth 5.1','GPS, GLONASS, BDS','None','None'),(40,'6.5 inches','IPS LCD','720 x 1600 pixels','None','8 MP (Wide)','1080p@30fps','5 MP','720p@30fps','Unisoc SC9863A','5000 mAh','None','Dual SIM (Nano-SIM, dual stand-by)','Android 11, Realme UI Go','4G','Wi-Fi 802.11 b/g/n','Bluetooth 4.2','GPS, GLONASS','None','None'),(41,'6.1 inches','Liquid Retina IPS LCD','828 x 1792 pixels','True Tone, Wide color (P3)','Dual 12 MP (Wide, UltraWide)','4K@24/30/60fps','12 MP','4K@24/30/60fps','Apple A13 Bionic','3110 mAh','18W fast charging','Dual SIM (Nano-SIM and eSIM)','iOS 13, upgradable to iOS 14','4G','Wi-Fi 802.11 a/b/g/n/ac/6','Bluetooth 5.0','GPS, GLONASS','IP68 dust/water resistant','Stereo speakers'),(42,'6.1 inches','Super Retina XDR OLED','1170 x 2532 pixels','HDR10, Dolby Vision, True Tone, Wide color (P3)','Dual 12 MP (Wide, UltraWide)','4K@24/30/60fps','12 MP','4K@24/30/60fps','Apple A14 Bionic','2815 mAh','20W fast charging','Dual SIM (Nano-SIM and eSIM)','iOS 14, upgradable to iOS 16','5G','Wi-Fi 802.11 a/b/g/n/ac/6','Bluetooth 5.0','GPS, GLONASS, GALILEO, QZSS','IP68 dust/water resistant','Stereo speakers'),(43,'6.1 inches','Super Retina XDR OLED','1170 x 2532 pixels','HDR10, Dolby Vision, True Tone, Wide color (P3)','Dual 12 MP (Wide, UltraWide)','4K@24/30/60fps','12 MP','4K@24/30/60fps','Apple A15 Bionic','3240 mAh','23W fast charging','Dual SIM (Nano-SIM and eSIM)','iOS 15, upgradable to iOS 16','5G','Wi-Fi 802.11 a/b/g/n/ac/6','Bluetooth 5.0','GPS, GLONASS, GALILEO, QZSS','IP68 dust/water resistant','Stereo speakers'),(44,'6.1 inches','Super Retina XDR OLED','1170 x 2532 pixels','HDR10, Dolby Vision, True Tone, Wide color (P3)','Dual 12 MP (Wide, UltraWide)','4K@24/30/60fps','12 MP','4K@24/30/60fps','Apple A15 Bionic','3279 mAh','25W fast charging','Dual SIM (Nano-SIM and eSIM)','iOS 16','5G','Wi-Fi 802.11 a/b/g/n/ac/6e','Bluetooth 5.3','GPS, GLONASS, GALILEO, QZSS','IP68 dust/water resistant','Stereo speakers'),(45,'6.1 inches','Super Retina XDR OLED','1170 x 2532 pixels','HDR10, Dolby Vision, True Tone, Wide color (P3)','Dual 12 MP (Wide, UltraWide)','4K@24/30/60fps','12 MP','4K@24/30/60fps','Apple A16 Bionic','3350 mAh','30W fast charging','Dual SIM (Nano-SIM and eSIM)','iOS 17','5G','Wi-Fi 802.11 a/b/g/n/ac/6e','Bluetooth 5.3','GPS, GLONASS, GALILEO, QZSS','IP68 dust/water resistant','Stereo speakers'),(46,'6.7 inches','Super Retina XDR OLED','1284 x 2778 pixels','HDR10, Dolby Vision, True Tone, Wide color (P3)','Dual 12 MP (Wide, UltraWide)','4K@24/30/60fps','12 MP','4K@24/30/60fps','Apple A15 Bionic','4325 mAh','30W fast charging','Dual SIM (Nano-SIM and eSIM)','iOS 16','5G','Wi-Fi 802.11 a/b/g/n/ac/6e','Bluetooth 5.3','GPS, GLONASS, GALILEO, QZSS','IP68 dust/water resistant','Stereo speakers'),(47,'6.7 inches','Super Retina XDR OLED','1290 x 2796 pixels','HDR10, Dolby Vision, True Tone, Wide color (P3)','Dual 12 MP (Wide, UltraWide)','4K@24/30/60fps','12 MP','4K@24/30/60fps','Apple A16 Bionic','4325 mAh','30W fast charging','Dual SIM (Nano-SIM and eSIM)','iOS 17','5G','Wi-Fi 802.11 a/b/g/n/ac/6e','Bluetooth 5.3','GPS, GLONASS, GALILEO, QZSS','IP68 dust/water resistant','Stereo speakers'),(48,'6.1 inches','Super Retina XDR OLED','1170 x 2532 pixels','ProMotion, HDR10, Dolby Vision, True Tone, Wide color (P3)','Triple 12 MP (Wide, UltraWide, Telephoto)','4K@24/30/60fps','12 MP','4K@24/30/60fps','Apple A17 Pro','3300 mAh','30W fast charging','Dual SIM (Nano-SIM and eSIM)','iOS 17','5G','Wi-Fi 802.11 a/b/g/n/ac/6e','Bluetooth 5.3','GPS, GLONASS, GALILEO, QZSS','IP68 dust/water resistant','Stereo speakers'),(49,'6.1 inches','Super Retina XDR OLED','1170 x 2532 pixels','ProMotion, HDR10, Dolby Vision, True Tone, Wide color (P3)','Triple 48 MP (Wide), 12 MP (Telephoto), 12 MP (UltraWide)','4K@24/30/60fps','12 MP','4K@24/30/60fps','Apple A16 Bionic 6 nhân','3200 mAh','30W fast charging','Dual SIM (Nano-SIM and eSIM)','iOS 16','5G','Wi-Fi 802.11 a/b/g/n/ac/6e','Bluetooth 5.3','GPS, GLONASS, GALILEO, QZSS','IP68 dust/water resistant','Stereo speakers'),(50,'6.7 inches','Super Retina XDR OLED','1290 x 2796 pixels','ProMotion, HDR10, Dolby Vision, True Tone, Wide color (P3)','Triple 48 MP (Wide), 12 MP (Telephoto), 12 MP (UltraWide)','4K@24/30/60fps','12 MP','4K@24/30/60fps','Apple A17 Pro 6 nhân','4422 mAh','30W fast charging','Dual SIM (Nano-SIM and eSIM)','iOS 17','5G','Wi-Fi 802.11 a/b/g/n/ac/6e','Bluetooth 5.4','GPS, GLONASS, GALILEO, QZSS','IP68 dust/water resistant','Stereo speakers');
/*!40000 ALTER TABLE `chitietsanpham` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `colors`
--

DROP TABLE IF EXISTS `colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `colors` (
  `MaMau` int NOT NULL,
  `MaSanPham` int DEFAULT NULL,
  `TenMau` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`MaMau`),
  KEY `MaSanPham` (`MaSanPham`),
  CONSTRAINT `colors_ibfk_1` FOREIGN KEY (`MaSanPham`) REFERENCES `sanpham` (`MaSanPham`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `colors`
--

LOCK TABLES `colors` WRITE;
/*!40000 ALTER TABLE `colors` DISABLE KEYS */;
INSERT INTO `colors` VALUES (1,1,'Vàng'),(2,1,'Xám'),(3,1,'Tím'),(4,1,'Đen'),(5,2,'Trắng'),(6,2,'Đỏ'),(7,2,'Xanh'),(8,2,'Đen'),(9,3,'Trắng'),(10,3,'Đỏ'),(11,3,'Xanh'),(12,3,'Đen'),(13,4,'Kem'),(14,4,'Đen'),(15,4,'Xanh'),(16,5,'Đen'),(17,5,'Vàng'),(18,5,'Trắng'),(19,5,'Tím'),(20,6,'Đen'),(21,6,'Xanh nhạt'),(22,6,'Vàng'),(24,7,'Đen'),(25,7,'Tím'),(26,7,'Xanh nhạt'),(27,8,'Đen'),(28,8,'Trắng'),(29,8,'Xanh'),(30,9,'Đen'),(31,9,'Trắng'),(32,9,'Xanh'),(33,10,'Đen'),(34,10,'Vàng'),(35,10,'Xanh'),(36,11,'Tím'),(37,11,'Đen'),(38,11,'Trắng'),(39,12,'Xanh lá'),(40,12,'Đen'),(41,12,'Tím'),(42,13,'Đen'),(43,13,'Vàng'),(44,13,'Xanh lá'),(45,14,'Vàng'),(46,14,'Đen'),(47,14,'Xám'),(48,15,'Xanh lá'),(49,15,'Đen'),(50,15,'Xanh dương'),(51,16,'Xanh'),(52,16,'Đen'),(53,16,'Trắng'),(54,17,'Xanh dương'),(55,17,'Xanh lá'),(56,17,'Đen'),(57,18,'Vàng'),(58,18,'Xám'),(59,18,'Xanh lá'),(60,18,'Xanh dương'),(61,19,'Xanh dương'),(62,19,'Xanh lá'),(63,19,'Đen'),(64,20,'Đen'),(65,20,'Xanh lá'),(66,21,'Đen'),(67,21,'Vàng'),(68,22,'Trắng'),(69,22,'Đen'),(70,23,'Xanh'),(71,23,'Đen'),(72,24,'Xanh dương'),(73,24,'Xám'),(74,25,'Đen'),(75,25,'Xanh'),(76,26,'Xanh Dương'),(77,26,'Xanh Đen'),(78,27,'Đen'),(79,27,'Vàng'),(80,28,'Đen'),(81,28,'Xanh'),(82,29,'Đen'),(83,29,'Xanh dương'),(84,30,'Vàng'),(85,30,'Xanh'),(86,31,'Vàng'),(87,31,'Đen'),(88,32,'Xanh'),(89,32,'Đen'),(90,33,'Đen'),(91,33,'Vàng'),(92,34,'Vàng'),(93,34,'Đen'),(94,35,'Đen'),(95,35,'Xanh'),(96,36,'Vàng'),(97,36,'Đen'),(98,36,'Xanh'),(99,37,'Xanh'),(100,37,'Đen'),(101,38,'Trắng'),(102,38,'Xanh'),(103,39,'Xanh dương'),(104,39,'Đen'),(105,40,'Xanh'),(106,40,'Đen'),(107,41,'Vàng'),(108,41,'Trắng'),(109,41,'Đỏ'),(110,41,'Xanh'),(111,41,'Đen'),(112,41,'Tím'),(113,42,'Tím'),(114,42,'Trắng'),(115,42,'Đỏ'),(116,42,'Xanh dương'),(117,42,'Đen'),(118,42,'Xanh lá'),(119,43,'Hồng'),(120,43,'Trắng'),(121,43,'Đỏ'),(122,43,'Xanh dương'),(123,43,'Đen'),(124,43,'Xanh lá'),(125,44,'Xanh'),(126,44,'Trắng'),(127,44,'Đỏ'),(128,44,'Vàng'),(129,44,'Đen'),(130,44,'Tím'),(131,45,'Xanh lá'),(132,45,'Hồng'),(133,45,'Vàng'),(134,45,'Xanh dương'),(135,45,'Đen'),(136,46,'Tím'),(137,46,'Trắng'),(138,46,'Đỏ'),(139,46,'Xanh'),(140,46,'Đen'),(141,46,'Vàng'),(142,47,'Xanh Dương'),(143,47,'Xanh Lá'),(144,47,'Đen'),(145,47,'Hồng'),(146,47,'Vàng'),(147,48,'Titan Tự Nhiên'),(148,48,'Titan Đen'),(149,48,'Titan Trắng'),(150,48,'Titan Xanh'),(151,49,'Tím'),(152,49,'Bạc'),(153,49,'Vàng'),(154,49,'Đen'),(155,50,'Titan Đen'),(156,50,'Titan Trắng'),(157,50,'Titan Xanh'),(158,50,'Titan Tự Nhiên');
/*!40000 ALTER TABLE `colors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feedback` (
  `FeedbackID` int NOT NULL AUTO_INCREMENT,
  `MaKhachHang` int DEFAULT NULL,
  `MaHoaDon` int DEFAULT NULL,
  `HoTen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `MaSanPham` int DEFAULT NULL,
  `SoSao` int DEFAULT NULL,
  `BinhLuan` text,
  `Ngay` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`FeedbackID`),
  KEY `MaSanPham` (`MaSanPham`),
  KEY `MaHoaDon` (`MaHoaDon`),
  KEY `MaKhachHang` (`MaKhachHang`),
  CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`MaSanPham`) REFERENCES `sanpham` (`MaSanPham`),
  CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadon` (`MaHoaDon`),
  CONSTRAINT `feedback_ibfk_3` FOREIGN KEY (`MaKhachHang`) REFERENCES `khachhang` (`MaKhachHang`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback`
--

LOCK TABLES `feedback` WRITE;
/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `giasanpham`
--

DROP TABLE IF EXISTS `giasanpham`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `giasanpham` (
  `MaGia` int NOT NULL AUTO_INCREMENT,
  `MaRam` int DEFAULT NULL,
  `MaSanPham` int DEFAULT NULL,
  `MaMau` int DEFAULT NULL,
  `GiaCu` int DEFAULT NULL,
  `GiaMoi` int DEFAULT NULL,
  `SoLuong` int DEFAULT NULL,
  PRIMARY KEY (`MaGia`),
  KEY `MaRam` (`MaRam`),
  KEY `MaSanPham` (`MaSanPham`),
  KEY `MaMau` (`MaMau`),
  CONSTRAINT `giasanpham_ibfk_1` FOREIGN KEY (`MaRam`) REFERENCES `ram_rom_option` (`MaRam`),
  CONSTRAINT `giasanpham_ibfk_2` FOREIGN KEY (`MaSanPham`) REFERENCES `sanpham` (`MaSanPham`),
  CONSTRAINT `giasanpham_ibfk_3` FOREIGN KEY (`MaMau`) REFERENCES `colors` (`MaMau`)
) ENGINE=InnoDB AUTO_INCREMENT=387 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `giasanpham`
--

LOCK TABLES `giasanpham` WRITE;
/*!40000 ALTER TABLE `giasanpham` DISABLE KEYS */;
INSERT INTO `giasanpham` VALUES (1,1,1,1,28000000,26000000,100),(2,1,1,2,28000000,26000000,100),(3,1,1,3,28000000,26000000,100),(4,1,1,4,28000000,26000000,100),(5,2,1,1,30000000,28000000,100),(6,2,1,2,30000000,28000000,100),(7,2,1,3,30000000,28000000,100),(8,2,1,4,30000000,28000000,100),(9,3,1,1,35000000,32000000,100),(10,3,1,2,35000000,32000000,100),(11,3,1,3,35000000,32000000,100),(12,3,1,4,35000000,32000000,100),(13,4,2,5,25000000,23000000,100),(14,4,2,6,25000000,23000000,100),(15,4,2,7,25000000,23000000,100),(16,4,2,8,25000000,23000000,100),(17,5,2,5,27000000,25000000,100),(18,5,2,6,27000000,25000000,100),(19,5,2,7,27000000,25000000,100),(20,5,2,8,27000000,25000000,100),(21,6,2,5,32000000,30000000,100),(22,6,2,6,32000000,30000000,100),(23,6,2,7,32000000,30000000,100),(24,6,2,8,32000000,30000000,100),(25,7,3,9,26000000,24000000,100),(26,7,3,10,26000000,24000000,100),(27,7,3,11,26000000,24000000,100),(28,7,3,12,26000000,24000000,100),(29,8,3,9,28000000,26000000,100),(30,8,3,10,28000000,26000000,100),(31,8,3,11,28000000,26000000,100),(32,8,3,12,28000000,26000000,100),(33,9,3,9,33000000,31000000,100),(34,9,3,10,33000000,31000000,100),(35,9,3,11,33000000,31000000,100),(36,9,3,12,33000000,31000000,100),(37,10,4,13,33000000,31000000,100),(38,10,4,14,33000000,31000000,100),(39,10,4,15,33000000,31000000,100),(40,11,4,13,35000000,33000000,100),(41,11,4,14,35000000,33000000,100),(42,11,4,15,35000000,33000000,100),(43,12,4,13,40000000,38000000,100),(44,12,4,14,40000000,38000000,100),(45,12,4,15,40000000,38000000,100),(46,13,5,16,5000000,4500000,100),(47,14,5,16,6000000,5500000,100),(48,15,5,16,7000000,6500000,100),(49,13,5,17,5000000,4500000,100),(50,14,5,17,6000000,5500000,100),(51,15,5,17,7000000,6500000,100),(52,13,5,18,5000000,4500000,100),(53,14,5,18,6000000,5500000,100),(54,15,5,18,7000000,6500000,100),(55,13,5,19,5000000,4500000,100),(56,14,5,19,6000000,5500000,100),(57,15,5,19,7000000,6500000,100),(58,16,6,20,6000000,5500000,100),(59,17,6,20,7000000,6500000,100),(60,18,6,20,8000000,7500000,100),(61,16,6,21,6000000,5500000,100),(62,17,6,21,7000000,6500000,100),(63,18,6,21,8000000,7500000,100),(64,16,6,22,6000000,5500000,100),(65,17,6,22,7000000,6500000,100),(66,18,6,22,8000000,7500000,100),(67,19,7,24,9000000,8500000,100),(68,20,7,24,10000000,9500000,100),(69,21,7,24,11000000,10500000,100),(70,19,7,25,9000000,8500000,100),(71,20,7,25,10000000,9500000,100),(72,21,7,25,11000000,10500000,100),(73,19,7,26,9000000,8500000,100),(74,20,7,26,10000000,9500000,100),(75,21,7,26,11000000,10500000,100),(76,22,8,27,3000000,2800000,100),(77,23,8,27,3500000,3300000,100),(78,24,8,27,4000000,3800000,100),(79,22,8,28,3000000,2800000,100),(80,23,8,28,3500000,3300000,100),(81,24,8,28,4000000,3800000,100),(82,22,8,29,3000000,2800000,100),(83,23,8,29,3500000,3300000,100),(84,24,8,29,4000000,3800000,100),(85,25,9,30,8500000,8200000,50),(86,26,9,30,9500000,9200000,50),(87,25,9,31,8500000,8200000,50),(88,26,9,31,9500000,9200000,50),(89,25,9,32,8500000,8200000,50),(90,26,9,32,9500000,9200000,50),(91,27,10,33,5500000,5300000,100),(92,28,10,33,6500000,6300000,100),(93,27,10,34,5500000,5300000,100),(94,28,10,34,6500000,6300000,100),(95,27,10,35,5500000,5300000,100),(96,28,10,35,6500000,6300000,100),(97,29,11,36,9500000,9200000,100),(98,30,11,36,10500000,10200000,100),(99,29,11,37,9500000,9200000,100),(100,30,11,37,10500000,10200000,100),(101,29,11,38,9500000,9200000,100),(102,30,11,38,10500000,10200000,100),(103,31,12,39,8500000,8200000,100),(104,32,12,39,9500000,9200000,100),(105,31,12,40,8500000,8200000,100),(106,32,12,40,9500000,9200000,100),(107,31,12,41,8500000,8200000,100),(108,32,12,41,9500000,9200000,100),(109,33,13,42,5500000,5300000,100),(110,34,13,42,6500000,6300000,100),(111,33,13,43,5500000,5300000,100),(112,34,13,43,6500000,6300000,100),(113,33,13,44,5500000,5300000,100),(114,34,13,44,6500000,6300000,100),(115,35,14,45,12500000,12000000,100),(116,36,14,45,13500000,13000000,100),(117,35,14,46,12500000,12000000,100),(118,36,14,46,13500000,13000000,100),(119,35,14,47,12500000,12000000,100),(120,36,14,47,13500000,13000000,100),(121,37,15,48,15500000,15000000,100),(122,38,15,48,16500000,16000000,100),(123,37,15,49,15500000,15000000,100),(124,38,15,49,16500000,16000000,100),(125,37,15,50,15500000,15000000,100),(126,38,15,50,16500000,16000000,100),(127,39,16,51,18500000,18000000,100),(128,40,16,51,19500000,19000000,100),(129,39,16,52,18500000,18000000,100),(130,40,16,52,19500000,19000000,100),(131,39,16,53,18500000,18000000,100),(132,40,16,53,19500000,19000000,100),(133,41,17,54,5500000,5300000,100),(134,42,17,54,6500000,6300000,100),(135,41,17,55,5500000,5300000,100),(136,42,17,55,6500000,6300000,100),(137,41,17,56,5500000,5300000,100),(138,42,17,56,6500000,6300000,100),(139,43,18,57,5000000,4800000,100),(140,44,18,57,6000000,5800000,100),(141,43,18,58,5000000,4800000,100),(142,44,18,58,6000000,5800000,100),(143,43,18,59,5000000,4800000,100),(144,44,18,59,6000000,5800000,100),(145,43,18,60,5000000,4800000,100),(146,44,18,60,6000000,5800000,100),(147,45,19,61,4000000,3800000,100),(148,46,19,61,5000000,4800000,100),(149,45,19,62,4000000,3800000,100),(150,46,19,62,5000000,4800000,100),(151,45,19,63,4000000,3800000,100),(152,46,19,63,5000000,4800000,100),(153,47,20,64,4700000,4000000,100),(154,48,20,64,5700000,5000000,100),(155,47,20,65,4700000,4000000,100),(156,48,20,65,5700000,5000000,100),(157,49,21,66,25000000,23000000,50),(158,50,21,66,30000000,28000000,50),(159,49,21,67,25000000,23000000,50),(160,50,21,67,30000000,28000000,50),(161,51,22,68,31000000,29000000,50),(162,52,22,68,36000000,34000000,50),(163,51,22,69,31000000,29000000,50),(164,52,22,69,36000000,34000000,50),(165,53,23,70,18000000,16000000,50),(166,54,23,70,21000000,19000000,50),(167,53,23,71,18000000,16000000,50),(168,54,23,71,21000000,19000000,50),(169,55,24,72,16000000,14000000,50),(170,56,24,72,19000000,17000000,50),(171,55,24,73,16000000,14000000,50),(172,56,24,73,19000000,17000000,50),(173,57,25,74,6000000,5000000,50),(174,58,25,74,7000000,6000000,50),(175,57,25,75,6000000,5000000,50),(176,58,25,75,7000000,6000000,50),(177,59,26,76,8000000,7000000,50),(178,60,26,76,9000000,8000000,50),(179,59,26,77,8000000,7000000,50),(180,60,26,77,9000000,8000000,50),(181,61,27,78,7000000,6000000,50),(182,62,27,78,8000000,7000000,50),(183,61,27,79,7000000,6000000,50),(184,62,27,79,8000000,7000000,50),(185,63,28,80,7000000,6000000,50),(186,63,28,81,7000000,6000000,50),(187,64,29,82,5000000,4000000,50),(188,64,29,83,5000000,4000000,50),(189,65,30,84,4500000,3500000,50),(190,65,30,85,4500000,3500000,50),(191,66,31,86,10000000,8500000,30),(192,66,31,87,10000000,8500000,30),(193,67,32,88,6000000,5000000,40),(194,67,32,89,6000000,5000000,40),(195,68,33,90,4500000,4000000,35),(196,69,33,90,5000000,4500000,35),(197,68,33,91,4500000,4000000,35),(198,69,33,91,5000000,4500000,35),(199,70,34,92,5000000,4000000,45),(200,70,34,93,5000000,4000000,45),(201,71,35,94,3000000,2700000,50),(202,72,35,94,3200000,2900000,50),(203,73,35,94,4000000,3700000,50),(204,71,35,94,3000000,2700000,50),(205,72,35,94,3200000,2900000,50),(206,73,35,94,4000000,3700000,50),(207,74,36,96,3000000,2700000,50),(208,75,36,96,3200000,2900000,50),(209,74,36,97,3000000,2700000,50),(210,75,36,97,3200000,2900000,50),(211,74,36,98,3000000,2700000,50),(212,75,36,98,3200000,2900000,50),(213,76,37,99,3500000,3200000,50),(214,76,37,100,3500000,3200000,50),(215,77,38,101,15000000,13500000,30),(216,77,38,102,15000000,13500000,30),(217,78,39,103,2800000,2500000,40),(218,79,39,103,3100000,2800000,40),(219,78,39,104,2800000,2500000,40),(220,79,39,104,3100000,2800000,40),(221,80,40,105,3000000,2800000,60),(222,80,40,106,3000000,2800000,60),(223,81,41,107,10000000,8700000,50),(224,82,41,107,12000000,9900000,50),(225,83,41,107,20000000,18000000,50),(226,81,41,108,10000000,8700000,50),(227,82,41,108,12000000,9900000,50),(228,83,41,108,20000000,18000000,50),(229,81,41,109,10000000,8700000,50),(230,82,41,109,12000000,9900000,50),(231,83,41,109,20000000,18000000,50),(232,81,41,110,10000000,8700000,50),(233,82,41,110,12000000,9900000,50),(234,83,41,110,20000000,18000000,50),(235,81,41,111,10000000,8700000,50),(236,82,41,111,12000000,9900000,50),(237,83,41,111,20000000,18000000,50),(238,81,41,112,10000000,8700000,50),(239,82,41,112,12000000,9900000,50),(240,83,41,112,20000000,18000000,50),(241,84,42,113,13000000,11500000,50),(242,85,42,113,14000000,12500000,50),(243,86,42,113,22000000,20000000,50),(244,84,42,114,13000000,11500000,50),(245,85,42,114,14000000,12500000,50),(246,86,42,114,22000000,20000000,50),(247,84,42,115,13000000,11500000,50),(248,85,42,115,14000000,12500000,50),(249,86,42,115,22000000,20000000,50),(250,84,42,116,13000000,11500000,50),(251,85,42,116,14000000,12500000,50),(252,86,42,116,22000000,20000000,50),(253,84,42,117,13000000,11500000,50),(254,85,42,117,14000000,12500000,50),(255,86,42,117,22000000,20000000,50),(256,84,42,118,13000000,11500000,50),(257,85,42,118,14000000,12500000,50),(258,86,42,118,22000000,20000000,50),(259,87,43,119,15500000,12500000,50),(260,88,43,119,19000000,17000000,50),(261,89,43,119,27000000,25000000,50),(262,87,43,120,15500000,12500000,50),(263,88,43,120,19000000,17000000,50),(264,89,43,120,27000000,25000000,50),(265,87,43,121,15500000,12500000,50),(266,88,43,121,19000000,17000000,50),(267,89,43,121,27000000,25000000,50),(268,87,43,122,15500000,12500000,50),(269,88,43,122,19000000,17000000,50),(270,89,43,122,27000000,25000000,50),(271,87,43,123,15500000,12500000,50),(272,88,43,123,19000000,17000000,50),(273,89,43,123,27000000,25000000,50),(274,87,43,123,15500000,12500000,50),(275,88,43,123,19000000,17000000,50),(276,89,43,123,27000000,25000000,50),(277,90,44,125,18000000,16400000,50),(278,91,44,125,14000000,22000000,50),(279,92,44,125,26000000,24000000,50),(280,90,44,126,18000000,16400000,50),(281,91,44,126,14000000,22000000,50),(282,92,44,126,26000000,24000000,50),(283,90,44,127,18000000,16400000,50),(284,91,44,127,14000000,22000000,50),(285,92,44,127,26000000,24000000,50),(286,90,44,128,18000000,16400000,50),(287,91,44,128,14000000,22000000,50),(288,92,44,128,26000000,24000000,50),(289,90,44,129,18000000,16400000,50),(290,91,44,129,14000000,22000000,50),(291,92,44,129,26000000,24000000,50),(292,90,44,130,18000000,16400000,50),(293,91,44,130,14000000,22000000,50),(294,92,44,130,26000000,24000000,50),(295,93,45,131,21000000,19000000,50),(296,94,45,131,24000000,22600000,50),(297,95,45,131,31000000,28400000,50),(298,93,45,132,21000000,19000000,50),(299,94,45,132,24000000,22600000,50),(300,95,45,132,31000000,28400000,50),(301,93,45,133,21000000,19000000,50),(302,94,45,133,24000000,22600000,50),(303,95,45,133,31000000,28400000,50),(304,93,45,134,21000000,19000000,50),(305,94,45,134,24000000,22600000,50),(306,95,45,134,31000000,28400000,50),(307,93,45,135,21000000,19000000,50),(308,94,45,135,24000000,22600000,50),(309,95,45,135,31000000,28400000,50),(310,96,46,136,21000000,19400000,50),(311,97,46,136,26000000,24000000,50),(312,98,46,136,28000000,25500000,50),(313,96,46,137,21000000,19400000,50),(314,97,46,137,26000000,24000000,50),(315,98,46,137,28000000,25500000,50),(316,96,46,138,21000000,19400000,50),(317,97,46,138,26000000,24000000,50),(318,98,46,138,28000000,25500000,50),(319,96,46,139,21000000,19400000,50),(320,97,46,139,26000000,24000000,50),(321,98,46,139,28000000,25500000,50),(322,96,46,140,21000000,19400000,50),(323,97,46,140,26000000,24000000,50),(324,98,46,140,28000000,25500000,50),(325,96,46,141,21000000,19400000,50),(326,97,46,141,26000000,24000000,50),(327,98,46,141,28000000,25500000,50),(328,99,47,142,24000000,22000000,50),(329,100,47,142,26000000,24500000,50),(330,101,47,142,33000000,30500000,50),(331,99,47,143,24000000,22000000,50),(332,100,47,143,26000000,24500000,50),(333,101,47,143,33000000,30500000,50),(334,99,47,144,24000000,22000000,50),(335,100,47,144,26000000,24500000,50),(336,101,47,144,33000000,30500000,50),(337,99,47,145,24000000,22000000,50),(338,100,47,145,26000000,24500000,50),(339,101,47,145,33000000,30500000,50),(340,99,47,145,24000000,22000000,50),(341,100,47,145,26000000,24500000,50),(342,101,47,145,33000000,30500000,50),(343,102,48,147,30000000,28000000,50),(344,103,48,147,32000000,30000000,50),(345,104,48,147,36000000,34000000,50),(346,105,48,147,40000000,38000000,50),(347,102,48,148,30000000,28000000,50),(348,103,48,148,32000000,30000000,50),(349,104,48,148,36000000,34000000,50),(350,105,48,148,40000000,38000000,50),(351,102,48,149,30000000,28000000,50),(352,103,48,149,32000000,30000000,50),(353,104,48,149,36000000,34000000,50),(354,105,48,149,40000000,38000000,50),(355,102,48,150,30000000,28000000,50),(356,103,48,150,32000000,30000000,50),(357,104,48,150,36000000,34000000,50),(358,105,48,150,40000000,38000000,50),(359,106,49,151,28000000,24000000,50),(360,107,49,151,32000000,28000000,50),(361,108,49,151,36000000,34000000,50),(362,109,49,151,40000000,38000000,50),(363,106,49,152,28000000,24000000,50),(364,107,49,152,32000000,28000000,50),(365,108,49,152,36000000,34000000,50),(366,109,49,152,40000000,38000000,50),(367,106,49,153,28000000,24000000,50),(368,107,49,153,32000000,28000000,50),(369,108,49,153,36000000,34000000,50),(370,109,49,153,40000000,38000000,50),(371,106,49,154,28000000,24000000,50),(372,107,49,154,32000000,28000000,50),(373,108,49,154,36000000,34000000,50),(374,109,49,154,40000000,38000000,50),(375,110,50,155,34000000,29000000,50),(376,111,50,155,38000000,35000000,50),(377,112,50,155,46000000,43000000,50),(378,110,50,156,34000000,29000000,50),(379,111,50,156,38000000,35000000,50),(380,112,50,156,46000000,43000000,50),(381,110,50,157,34000000,29000000,50),(382,111,50,157,38000000,35000000,50),(383,112,50,157,46000000,43000000,50),(384,110,50,158,34000000,29000000,50),(385,111,50,158,38000000,35000000,50),(386,112,50,158,46000000,43000000,50);
/*!40000 ALTER TABLE `giasanpham` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `giohang`
--

DROP TABLE IF EXISTS `giohang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `giohang` (
  `MaHang` int NOT NULL AUTO_INCREMENT,
  `TenDangNhap` varchar(50) NOT NULL,
  `MaSanPham` int DEFAULT NULL,
  `DiaChiAnh` text,
  `TenSanPham` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `MauSac` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `KichThuoc` varchar(30) DEFAULT NULL,
  `GiaMoi` int DEFAULT NULL,
  `Soluong` int DEFAULT NULL,
  PRIMARY KEY (`MaHang`),
  KEY `MaSanPham` (`MaSanPham`),
  KEY `TenDangNhap` (`TenDangNhap`),
  CONSTRAINT `giohang_ibfk_1` FOREIGN KEY (`MaSanPham`) REFERENCES `sanpham` (`MaSanPham`),
  CONSTRAINT `giohang_ibfk_2` FOREIGN KEY (`TenDangNhap`) REFERENCES `khachhang` (`TenDangNhap`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `giohang`
--

LOCK TABLES `giohang` WRITE;
/*!40000 ALTER TABLE `giohang` DISABLE KEYS */;
INSERT INTO `giohang` VALUES (7,'1',2,'img/samsung/sm-s908_galaxys22ultra_front_phantomwhite_211119_1_3.jpg','Samsung Galaxy S22 Ultra','Trắng','12GB/512GB',25000000,1),(8,'1',26,'img/oppo/t_i_xu_ng_11__2_4.jpg','Oppo Reno 11 F 5G','Xanh Dương','12GB/256GB',8000000,1);
/*!40000 ALTER TABLE `giohang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hoadon`
--

DROP TABLE IF EXISTS `hoadon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hoadon` (
  `MaHoaDon` int NOT NULL AUTO_INCREMENT,
  `TenDangNhap` varchar(50) DEFAULT NULL,
  `NgayLap` date DEFAULT NULL,
  `TongTien` decimal(18,2) DEFAULT NULL,
  `TrangThai` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`MaHoaDon`),
  KEY `TenDangNhap` (`TenDangNhap`),
  CONSTRAINT `hoadon_ibfk_1` FOREIGN KEY (`TenDangNhap`) REFERENCES `khachhang` (`TenDangNhap`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hoadon`
--

LOCK TABLES `hoadon` WRITE;
/*!40000 ALTER TABLE `hoadon` DISABLE KEYS */;
INSERT INTO `hoadon` VALUES (1,'1','2025-06-27',5800000.00,'Chưa xác nhận');
/*!40000 ALTER TABLE `hoadon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `image`
--

DROP TABLE IF EXISTS `image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `image` (
  `MaHinhAnh` int NOT NULL AUTO_INCREMENT,
  `MaSanPham` int DEFAULT NULL,
  `MaMau` int DEFAULT NULL,
  `DiaChiAnh` text,
  PRIMARY KEY (`MaHinhAnh`),
  KEY `MaSanPham` (`MaSanPham`),
  KEY `MaMau` (`MaMau`),
  CONSTRAINT `image_ibfk_1` FOREIGN KEY (`MaSanPham`) REFERENCES `sanpham` (`MaSanPham`),
  CONSTRAINT `image_ibfk_2` FOREIGN KEY (`MaMau`) REFERENCES `colors` (`MaMau`)
) ENGINE=InnoDB AUTO_INCREMENT=158 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `image`
--

LOCK TABLES `image` WRITE;
/*!40000 ALTER TABLE `image` DISABLE KEYS */;
INSERT INTO `image` VALUES (1,1,1,'img/samsung/galaxy-s24-ultra-vang_1_3.jpg'),(2,1,2,'img/samsung/galaxy-s24-ultra-xam_1_3.jpg'),(3,1,3,'img/samsung/galaxy-s24-ultra-tim_1_3.jpg'),(4,1,4,'img/samsung/galaxy-s24-ultra-den-1_1_3.jpg'),(5,2,5,'img/samsung/sm-s908_galaxys22ultra_front_phantomwhite_211119_1_3.jpg'),(6,2,6,'img/samsung/sm-s908_galaxys22ultra_front_burgundy_211119_5.jpg'),(7,2,7,'img/samsung/sm-s908_galaxys22ultra_front_green_211119_3.jpg'),(8,2,8,'img/samsung/sm-s908_galaxys22ultra_front_phantomblack_211119_3.jpg'),(9,3,9,'img/samsung/s23-ultra-kem.jpg'),(10,3,10,'img/samsung/s23-ultra-tim.jpg'),(11,3,11,'img/samsung/s23-ultra-xanh.jpg'),(12,3,12,'img/samsung/s23-ultra-den.jpg'),(13,4,13,'img/samsung/galaxy-z-fold-5-kem-1.jpg'),(14,4,14,'img/samsung/galaxy-z-fold-5-xam-1.jpg'),(15,4,15,'img/samsung/samsung-galaxy-z-fold-5-256gb_1.jpg'),(16,5,16,'img/samsung/sm-a546_galaxy_a54_5g_awesome_graphite_front_3.jpg'),(17,5,17,'img/samsung/sm-a546_galaxy_a54_5g_awesome_lime_front_3.jpg'),(18,5,18,'img/samsung/samsung-galaxy-a54-5g-8gb-256gb-cu-tray-xuoc_1.jpg'),(19,5,19,'img/samsung/sm-a546_galaxy_a54_5g_awesome_violet_front_3.jpg'),(20,6,20,'img/samsung/sm-a356_galaxy_a35_awesome_navy_ui.jpg'),(21,6,21,'img/samsung/sm-a356_galaxy_a35_awesome_iceblue_ui.jpg'),(22,6,22,'img/samsung/sm-a356_galaxy_a35_awesome_lemon_ui.jpg'),(23,7,24,'img/samsung/sm-a556_galaxy_a55_awesome_navy_ui.jpg'),(24,7,25,'img/samsung/sm-a556_galaxy_a55_awesome_lilac_ui.jpg'),(25,7,26,'img/samsung/sm-a556_galaxy_a55_awesome_iceblue_ui.jpg'),(26,8,27,'img/samsung/galaxy-a05-_en.jpg'),(27,8,28,'img/samsung/a05trang_1.jpg'),(28,8,29,'img/samsung/a05xanh.jpg'),(29,9,30,'img/samsung/sm-a536_galaxy_a53_5g_black.jpg'),(30,9,31,'img/samsung/sm-a536_galaxy_a53_5g_white-001_1.jpg'),(31,9,32,'img/samsung/_sm-a536_04._device_design_m_1_2.jpg'),(32,10,33,'img/samsung/galaxy-a25-xanh-la.jpg'),(33,10,34,'img/samsung/galaxy-a25-xanh-vang.jpg'),(34,10,35,'img/samsung/galaxy-a25-xanh-duongnhat.jpg'),(35,11,36,'img/redmi/note-13-pro-plus-1.jpg'),(36,11,37,'img/redmi/note-13-pro-plus-2.jpg'),(37,11,38,'img/redmi/xiaomi-redmi-note-13-pro-plus_9_.jpg'),(38,12,39,'img/redmi/xiaomi-redmi-note-13-pro-4g_11_.jpg'),(39,12,40,'img/redmi/xiaomi-redmi-note-13-pro-4g_13_.jpg'),(40,12,41,'img/redmi/xiaomi-redmi-note-13-pro-4g_12_.jpg'),(41,13,42,'img/redmi/xiaomi-redmi-note-13_1__1_1.jpg'),(42,13,43,'img/redmi/download_3__11.jpg'),(43,13,44,'img/redmi/downloadnote_13.jpg'),(44,14,45,'img/redmi/t_i_xu_ng_22__6_1.jpg'),(45,14,46,'img/redmi/t_i_xu_ng_19__5_4.jpg'),(46,14,47,'img/redmi/t_i_xu_ng_20__5_2.jpg'),(47,15,48,'img/redmi/xiaomi-13t_2__2_2.jpg'),(48,15,49,'img/redmi/xiaomi-13t-pro_3__1_1.jpg'),(49,15,50,'img/redmi/xiaomi-13-t-pro-xanh-duong-thumb-600x600.jpg'),(50,16,51,'img/redmi/xiaomi-14-pre-xanh-la_1.jpg'),(51,16,52,'img/redmi/xiaomi-14-pre-den_1.jpg'),(52,16,53,'img/redmi/xiaomi-14-pre-trang_1.jpg'),(53,17,54,'img/redmi/13c-xanhduong-3.jpg'),(54,17,55,'img/redmi/13c-xanhla-2.jpg'),(55,17,56,'img/redmi/13c-den-1.jpg'),(56,18,57,'img/redmi/xiaomi-redmi-note-12-8gb-128gb_1__1_3.jpg'),(57,18,58,'img/redmi/rgt76878_1__4.jpg'),(58,18,59,'img/redmi/gtt_7766_3__1_5.jpg'),(59,18,60,'img/redmi/_76666_6__4.jpg'),(60,19,61,'img/redmi/redmi-a3-xanh.jpg'),(61,19,62,'img/redmi/redmi-a3-xanh-la.jpg'),(62,19,63,'img/redmi/redmi-a3-den.jpg'),(63,20,64,'img/redmi/xiaomi_poco_m5_negro_01_l_1_1.jpg'),(64,20,65,'img/redmi/fvssxa_5.jpg'),(65,21,66,'img/oppo/oppo-find-n3-flip.jpg'),(66,21,67,'img/oppo/oppo-find-n3-flip_4_.jpg'),(67,22,68,'img/oppo/oppo-find-x5-pro-trang_1.jpg'),(68,22,69,'img/oppo/oppo-find-x5-pro-den_1.jpg'),(69,23,70,'img/oppo/oppo-reno7-pro_1.jpg'),(70,23,71,'img/oppo/t_i_xu_ng_8_.jpg'),(71,24,72,'img/oppo/reno10_5g_-_combo_product_-_blue_1.jpg'),(72,24,73,'img/oppo/reno10_5g_-_combo_product_-_grey_-_copy_1.jpg'),(73,25,74,'img/oppo/combo_a78_-_black_-_rgb_1.jpg'),(74,25,75,'img/oppo/combo_a78_-_blue_-_rgb_1.jpg'),(75,26,76,'img/oppo/t_i_xu_ng_11__2_4.jpg'),(76,26,77,'img/oppo/t_i_xu_ng_12__4.jpg'),(77,27,78,'img/oppo/oppo-reno-8t-4g-256gb.jpg'),(78,27,79,'img/oppo/638106973185744517_oppo-reno8-t-4g-cam-4.jpg'),(79,28,80,'img/oppo/combo_a77s-_en_2_1.jpg'),(80,28,81,'img/oppo/combo_a77s-_xanh_1_1.jpg'),(81,29,82,'img/oppo/t_i_xu_ng_1__9.jpg'),(82,29,83,'img/oppo/iofiuahsb8jqpsu5.jpg'),(83,30,84,'img/oppo/combo_a17k_-_gold_-_cmyk_1.jpg'),(84,30,85,'img/oppo/combo_a17k_-_navy_-_cmyk_1.jpg'),(85,31,86,'img/realme/realme-11-vang-1_1.jpg'),(86,31,87,'img/realme/realme-11-xam-1_1.jpg'),(87,32,88,'img/realme/realme-c67-1_1.jpg'),(88,32,89,'img/realme/t_i_xu_ng_4__6_6.jpg'),(89,33,90,'img/realme/rgrgrtyt6_4__1_1.jpg'),(90,33,91,'img/realme/rgrgrtyt6_1_1.jpg'),(91,34,92,'img/realme/realme-c53-vang-1_1.jpg'),(92,34,93,'img/realme/realme-c53-den-1_1.jpg'),(93,35,94,'img/realme/realme-c51-den-011_1.jpg'),(94,35,95,'img/realme/realme-c51_2.jpg'),(95,36,96,'img/realme/3sdcsc_2.jpg'),(96,36,97,'img/realme/2sdcsc_2.jpg'),(97,36,98,'img/realme/1sdcsc_2.jpg'),(98,37,99,'img/realme/realme-c60-xanh.jpg'),(99,37,100,'img/realme/xiaomi-c60-den.jpg'),(100,38,101,'img/realme/avt-realme-11-pro-5g-trang.jpg'),(101,38,102,'img/realme/avt-realme-11-pro-5g-xanh-la.jpg'),(102,39,103,'img/realme/realme-note-50-blue-thumb-600x600.jpg'),(103,39,104,'img/realme/realme-note-50-black-thumb-600x600.jpg'),(104,40,105,'img/realme/5927466811_1683521435.jpg'),(105,40,106,'img/realme/realme_realme_c30s_garansi_resmi_full02_pqf3bde.jpg'),(106,41,107,'img/iphone/1_253_1.jpg'),(107,41,108,'img/iphone/3_225_1.jpg'),(108,41,109,'img/iphone/iphone-11-128gb.jpg'),(109,41,110,'img/iphone/5_158_1.jpg'),(110,41,111,'img/iphone/6_130_1.jpg'),(111,41,112,'img/iphone/2_242_1.jpg'),(112,42,113,'img/iphone/5_157.jpg'),(113,42,114,'img/iphone/6_129.jpg'),(114,42,115,'img/iphone/4_186.jpg'),(115,42,116,'img/iphone/3_224.jpg'),(116,42,117,'img/iphone/iphone-12.jpg'),(117,42,118,'img/iphone/2_241.jpg'),(118,43,119,'img/iphone/h_ng_4.jpg'),(119,43,120,'img/iphone/tr_ng_5.jpg'),(120,43,121,'img/iphone/13_4_7_2_7.jpg'),(121,43,122,'img/iphone/d_ng_3.jpg'),(122,43,123,'img/iphone/_en_2_5.jpg'),(123,43,124,'img/iphone/xnnah_kas_3.jpg'),(124,44,125,'img/iphone/photo_2022-09-28_21-58-51_1.jpg'),(125,44,126,'img/iphone/photo_2022-09-28_21-58-48_1.jpg'),(126,44,127,'img/iphone/photo_2022-09-28_21-58-54.jpg'),(127,44,128,'img/iphone/iphone-14_1.jpg'),(128,44,129,'img/iphone/photo_2022-09-28_21-58-57_2.jpg'),(129,44,130,'img/iphone/photo_2022-09-28_21-58-56_1.jpg'),(130,45,131,'img/iphone/iphone-15-128gb-xanh-la.jpg'),(131,45,132,'img/iphone/iphone-15-plus_1__1.jpg'),(132,45,133,'img/iphone/vn_iphone_15_yellow_pdp_image_position-1a_yellow_color_1_4.jpg'),(133,45,134,'img/iphone/iphone-15-128gb-xanh-duong.jpg'),(134,45,135,'img/iphone/iphone-15-128-gbden.jpg'),(135,46,136,'img/iphone/photo_2022-09-28_21-58-56_5.jpg'),(136,46,137,'img/iphone/photo_2022-09-28_21-58-48_5.jpg'),(137,46,138,'img/iphone/photo_2022-09-28_21-58-54_5.jpg'),(138,46,139,'img/iphone/photo_2022-09-28_21-58-51_5.jpg'),(139,46,140,'img/iphone/photo_2022-09-28_21-58-57_7.jpg'),(140,46,141,'img/iphone/iphone-14-storage-select-202209-6-1inch-y889-1-f24906a2-7de6-4447-bc5c-2a90c8616dd4.jpg'),(141,47,142,'img/iphone/iphone-15-plus-update-02_6.jpg'),(142,47,143,'img/iphone/iphone-15-plus-update-03_6.jpg'),(143,47,144,'img/iphone/iphone-15-plus-update-01_6.jpg'),(144,47,145,'img/iphone/iphone-15-plus-update-04_6.jpg'),(145,47,146,'img/iphone/iphone-15-plus-256gb-color-yellow-image_1.jpg'),(146,48,147,'img/iphone/iphone15-pro-nau_1_.webp'),(147,48,148,'img/iphone/iphone15-pro-den_1.jpg'),(148,48,149,'img/iphone/iphone15-pro-trang_1__1.jpg'),(149,48,150,'img/iphone/iphone15-pro-xanh_1_.jpg'),(150,49,151,'img/iphone/iphone-14-pro_2__4.jpg'),(151,49,152,'img/iphone/b_c_1_1.jpg'),(152,49,153,'img/iphone/v_ng_12.jpg'),(153,49,154,'img/iphone/x_m_16.jpg'),(154,50,155,'img/iphone/vn_iphone_15_pro_black_titanium_pdp_image_position-1a_black_titanium_color.jpg'),(155,50,156,'img/iphone/iphone15-pro-max-titan-trang.jpg'),(156,50,157,'img/iphone/vn_iphone_15_pro_blue_titanium_pdp_image_position-1a_blue_titanium_color.jpg'),(157,50,158,'img/iphone/iphone-15-pro-max_3.jpg');
/*!40000 ALTER TABLE `image` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `khachhang`
--

DROP TABLE IF EXISTS `khachhang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `khachhang` (
  `MaKhachHang` int NOT NULL AUTO_INCREMENT,
  `TenDangNhap` varchar(50) NOT NULL,
  `MatKhau` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `HoTen` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `GioiTinh` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `SoDienThoai` varchar(15) NOT NULL,
  `DiaChi` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `NgaySinh` date DEFAULT NULL,
  `TongTienThanhToan` decimal(18,2) DEFAULT NULL,
  `HangThanhVien` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`MaKhachHang`,`TenDangNhap`),
  UNIQUE KEY `TenDangNhap` (`TenDangNhap`),
  UNIQUE KEY `MaKhachHang` (`MaKhachHang`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `khachhang`
--

LOCK TABLES `khachhang` WRITE;
/*!40000 ALTER TABLE `khachhang` DISABLE KEYS */;
INSERT INTO `khachhang` VALUES (1,'1','L1234567','Huỳnh Lê Công Lập',NULL,'0764806087','123 NTH','lapmo843@gmail.com','2004-12-01',NULL,NULL);
/*!40000 ALTER TABLE `khachhang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ram_rom_option`
--

DROP TABLE IF EXISTS `ram_rom_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ram_rom_option` (
  `MaRam` int NOT NULL,
  `MaSanPham` int DEFAULT NULL,
  `KichThuoc` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`MaRam`),
  KEY `MaSanPham` (`MaSanPham`),
  CONSTRAINT `ram_rom_option_ibfk_1` FOREIGN KEY (`MaSanPham`) REFERENCES `sanpham` (`MaSanPham`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ram_rom_option`
--

LOCK TABLES `ram_rom_option` WRITE;
/*!40000 ALTER TABLE `ram_rom_option` DISABLE KEYS */;
INSERT INTO `ram_rom_option` VALUES (1,1,'12GB/256GB'),(2,1,'12GB/512GB'),(3,1,'12GB/1TB'),(4,2,'12GB/256GB'),(5,2,'12GB/512GB'),(6,2,'12GB/1TB'),(7,3,'12GB/256GB'),(8,3,'12GB/512GB'),(9,3,'12GB/1TB'),(10,4,'12GB/256GB'),(11,4,'12GB/512GB'),(12,4,'12GB/1TB'),(13,5,'4GB/64GB'),(14,5,'6GB/128GB'),(15,5,'8GB/128GB'),(16,6,'4GB/64GB'),(17,6,'6GB/128GB'),(18,6,'8GB/128GB'),(19,7,'6GB/128GB'),(20,7,'8GB/256GB'),(21,7,'12GB/256GB'),(22,8,'3GB/32GB'),(23,8,'4GB/64GB'),(24,8,'6GB/128GB'),(25,9,'6GB/128GB'),(26,9,'8GB/256GB'),(27,10,'4GB/64GB'),(28,10,'6GB/128GB'),(29,11,'6GB/128GB'),(30,11,'8GB/256GB'),(31,12,'6GB/128GB'),(32,12,'8GB/256GB'),(33,13,'4GB/64GB'),(34,13,'6GB/128GB'),(35,14,'6GB/128GB'),(36,14,'8GB/256GB'),(37,15,'8GB/128GB'),(38,15,'12GB/256GB'),(39,16,'8GB/128GB'),(40,16,'12GB/256GB'),(41,17,'4GB/64GB'),(42,17,'6GB/128GB'),(43,18,'4GB/64GB'),(44,18,'6GB/128GB'),(45,19,'4GB/64GB'),(46,19,'6GB/128GB'),(47,20,'6GB/128GB'),(48,20,'8GB/256GB'),(49,21,'8GB/256GB'),(50,21,'12GB/512GB'),(51,22,'12GB/256GB'),(52,22,'12GB/512GB'),(53,23,'8GB/128GB'),(54,23,'12GB/256GB'),(55,24,'8GB/128GB'),(56,24,'12GB/256GB'),(57,25,'4GB/128GB'),(58,25,'6GB/256GB'),(59,26,'8GB/128GB'),(60,26,'12GB/256GB'),(61,27,'6GB/128GB'),(62,27,'8GB/256GB'),(63,28,'8GB/128GB'),(64,29,'4GB/128GB'),(65,30,'3GB/64GB'),(66,31,'8GB/128GB'),(67,32,'8GB/128GB'),(68,33,'6GB/128GB'),(69,33,'8GB/256GB'),(70,34,'8GB/256GB'),(71,35,'3GB/64GB'),(72,35,'4GB/128GB'),(73,35,'6GB/256GB'),(74,36,'3GB/32GB'),(75,36,'4GB/64GB'),(76,37,'4GB/64GB'),(77,38,'12GB/512GB'),(78,39,'3GB/64GB'),(79,39,'4GB/128GB'),(80,40,'3GB/64GB'),(81,41,'4GB/64GB'),(82,41,'4GB/128GB'),(83,41,'4GB/256GB'),(84,42,'4GB/64GB'),(85,42,'4GB/128GB'),(86,42,'4GB/256GB'),(87,43,'4GB/128GB'),(88,43,'4GB/256GB'),(89,43,'4GB/512GB'),(90,44,'6GB/128GB'),(91,44,'6GB/256GB'),(92,44,'6GB/512GB'),(93,45,'6GB/128GB'),(94,45,'6GB/256GB'),(95,45,'6GB/512GB'),(96,46,'6GB/128GB'),(97,46,'6GB/256GB'),(98,46,'6GB/512GB'),(99,47,'6GB/128GB'),(100,47,'6GB/256GB'),(101,47,'6GB/512GB'),(102,48,'8GB/128GB'),(103,48,'8GB/256GB'),(104,48,'8GB/512GB'),(105,48,'8GB/1TB'),(106,49,'6GB/128GB'),(107,49,'6GB/256GB'),(108,49,'6GB/512GB'),(109,49,'6GB/1TB'),(110,50,'8GB/256GB'),(111,50,'8GB/512GB'),(112,50,'8GB/1TB');
/*!40000 ALTER TABLE `ram_rom_option` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sanpham`
--

DROP TABLE IF EXISTS `sanpham`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sanpham` (
  `MaSanPham` int NOT NULL AUTO_INCREMENT,
  `TenSanPham` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Hang` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `NgayNhap` date DEFAULT NULL,
  PRIMARY KEY (`MaSanPham`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sanpham`
--

LOCK TABLES `sanpham` WRITE;
/*!40000 ALTER TABLE `sanpham` DISABLE KEYS */;
INSERT INTO `sanpham` VALUES (1,'Samsung Galaxy S24 Ultra','Samsung','2024-05-24'),(2,'Samsung Galaxy S22 Ultra','Samsung','2024-05-24'),(3,'Samsung Galaxy S23 Ultra','Samsung','2024-05-24'),(4,'Samsung Galaxy Fold 5','Samsung','2024-05-24'),(5,'Samsung Galaxy A54','Samsung','2024-05-24'),(6,'Samsung Galaxy A35 5G','Samsung','2024-05-24'),(7,'Samsung Galaxy A55 5G','Samsung','2024-05-24'),(8,'Samsung Galaxy A05','Samsung','2024-05-24'),(9,'Samsung Galaxy A53 5G','Samsung','2024-05-24'),(10,'Samsung Galaxy A25 5G','Samsung','2024-05-24'),(11,'Xiaomi Redmi Note 13 Pro Plus','Xiaomi','2024-05-24'),(12,'Xiaomi Redmi Note 13 Pro 4G','Xiaomi','2024-05-24'),(13,'Xiaomi Redmi Note 13','Xiaomi','2024-05-24'),(14,'Xiaomi Poco X6 Pro 5G','Xiaomi','2024-05-24'),(15,'Xiaomi 13T Pro 5G','Xiaomi','2024-05-24'),(16,'Xiaomi 14','Xiaomi','2024-05-24'),(17,'Xiaomi Redmi 13C','Xiaomi','2024-05-24'),(18,'Xiaomi Redmi Note 12','Xiaomi','2024-05-24'),(19,'Xiaomi Redmi A3','Xiaomi','2024-05-24'),(20,'Poco M5','Poco','2024-05-24'),(21,'Oppo Find N3 Flip','Oppo','2024-05-24'),(22,'Oppo Find X5 Pro','Oppo','2024-05-24'),(23,'Oppo Reno 7 Pro','Oppo','2024-05-24'),(24,'Oppo Reno 10 5G','Oppo','2024-05-24'),(25,'Oppo A78 4G','Oppo','2024-05-24'),(26,'Oppo Reno 11 F 5G','Oppo','2024-05-25'),(27,'Oppo Reno 8T 4G','Oppo','2024-05-25'),(28,'Oppo A77s','Oppo','2024-05-25'),(29,'Oppo A18','Oppo','2024-05-25'),(30,'Oppo A17K','Oppo','2024-05-25'),(31,'Realme 11','Realme','2024-05-25'),(32,'Realme C67','Realme','2024-05-25'),(33,'Realme C55','Realme','2024-05-25'),(34,'Realme C53','Realme','2024-05-25'),(35,'Realme C51','Realme','2024-05-25'),(36,'Realme C33','Realme','2024-05-25'),(37,'Realme C60','Realme','2024-05-25'),(38,'Realme 11 Pro+ 5G','Realme','2024-05-25'),(39,'Realme Note 50','Realme','2024-05-25'),(40,'Realme C30s','Realme','2024-05-25'),(41,'iPhone 11','Apple','2024-05-25'),(42,'iPhone 12','Apple','2024-05-25'),(43,'iPhone 13','Apple','2024-05-25'),(44,'iPhone 14','Apple','2024-05-25'),(45,'iPhone 15','Apple','2024-05-25'),(46,'iPhone 14 Plus','Apple','2024-05-25'),(47,'iPhone 15 Plus','Apple','2024-05-25'),(48,'iPhone 15 Pro','Apple','2024-05-25'),(49,'iPhone 14 Pro','Apple','2024-05-25'),(50,'iPhone 15 Pro Max','Apple','2024-05-25');
/*!40000 ALTER TABLE `sanpham` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `video`
--

DROP TABLE IF EXISTS `video`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `video` (
  `MaVideo` int NOT NULL AUTO_INCREMENT,
  `MaSanPham` int DEFAULT NULL,
  `DiaChiVideo` text,
  PRIMARY KEY (`MaVideo`),
  KEY `MaSanPham` (`MaSanPham`),
  CONSTRAINT `video_ibfk_1` FOREIGN KEY (`MaSanPham`) REFERENCES `sanpham` (`MaSanPham`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `video`
--

LOCK TABLES `video` WRITE;
/*!40000 ALTER TABLE `video` DISABLE KEYS */;
INSERT INTO `video` VALUES (1,1,'https://drive.google.com/file/d/1eCMXNOLLs_TmaLoouASZECJRFbQ34I3a/preview'),(2,2,'https://drive.google.com/file/d/1XqTu9_2YsqMV4ZL44l64QuYdUNbM2Iwz/preview'),(3,3,'https://drive.google.com/file/d/1RVQzTf5ixB3dH3_QxmlKgKvQjlDLZo-_/preview'),(4,4,'https://drive.google.com/file/d/1mS7NW8wviIjguO4L67yphau4ebiE_Wqp/preview'),(5,5,'https://drive.google.com/file/d/1v4_gYEQK-FVIXosWeTDTgNMe_Ukxs2EW/preview'),(6,6,'https://drive.google.com/file/d/1F9ozNrMeJblfP0BCZCVNxkQY4zLj1MJj/preview'),(7,7,'https://drive.google.com/file/d/1C-RuX0natx0LHnz9TE_c_IqO3ddS1mSn/preview'),(8,8,'https://drive.google.com/file/d/1rJmWyHUS5kTdMTVkAc812RSqaBEqffF9/preview'),(9,9,'https://drive.google.com/file/d/1z57bhHWZgZNlKiWUAaIY787cL3DohP8S/preview'),(10,10,'https://drive.google.com/file/d/1aF8dOH7BOtEFHL4Nhrs43sMAgB8iN35J/preview'),(11,11,'https://drive.google.com/file/d/1VAFN4chJrfwWW7LAVnntiw2cDFEKNuan/preview'),(12,12,'https://drive.google.com/file/d/11tH8CjAkZQhtTG6UBmKqyltrnBE31hvf/preview'),(13,13,'https://drive.google.com/file/d/11tH8CjAkZQhtTG6UBmKqyltrnBE31hvf/preview'),(14,14,'https://drive.google.com/file/d/1hGfI7cLSTorP-lSJlPfq6_nGsmpVXEeR/preview'),(15,15,'https://drive.google.com/file/d/1hc1ernTUJotNOnOCTSwK_9QokEUU9MLx/preview'),(16,16,'https://drive.google.com/file/d/13hJ2Y9DLaHLa28xJcL_UruhWV0J0R_Ny/preview'),(17,17,'https://drive.google.com/file/d/13gC6Bw0nanIGBCNMpiQeN7F4KkgPqch4/preview'),(18,18,'https://drive.google.com/file/d/1uJjQn1sH4ryQPZ6KxhHH8948vFqUus1e/preview'),(19,19,'https://drive.google.com/file/d/1tCHAqp4WzgjK8qYU3Ju8IxjGsIrILLSq/preview'),(20,20,'https://drive.google.com/file/d/1cVTEyAZsHK9cKs3QePlfvGm3cdO1D4iP/preview'),(21,21,'https://drive.google.com/file/d/15Blv--_jczvvrdMv_V4HmuBC9wLv7wrs/preview'),(22,22,'https://drive.google.com/file/d/10HDuiK1neQd7YIpQ3qf6Ccfrd21geLOS/preview'),(23,23,'https://drive.google.com/file/d/1Ks8UqbOYXyFZfMgMv8rzjlrK0SV7nR3Q/preview'),(24,24,'https://drive.google.com/file/d/1jvr7tHb6Rrk5ADywB9mQTnCekObbMtiy/preview'),(25,25,'https://drive.google.com/file/d/1O0a2vUD1Xx8h3RFb80mO3RkCxEFSWPS4/preview'),(26,26,'https://drive.google.com/file/d/1swvVtmlAXXdDIqPIYHw28EaOQ0kbXPmC/preview'),(27,27,'https://drive.google.com/file/d/14zAldRX9Gta8fEr-I-C8LqfysSjsbjMq/preview'),(28,28,'https://drive.google.com/file/d/13Tn0-UyMA0dpMvBLLllwxrOomhlKnd-t/preview'),(29,29,'https://drive.google.com/file/d/1hudzQ47qArILqZYTJr4iugOntl3zfUo4/preview'),(30,30,'https://drive.google.com/file/d/1eyLAmGRkzIqgLqIf6y0vQL-1Sq8oYQEZ/preview'),(31,31,'https://drive.google.com/file/d/1BV8pjdv_RdASVbNnE77xC6bFV9YIIfvg/preview'),(32,32,'https://drive.google.com/file/d/1I5I0rH1wNKuiP3T3B_9RPg4cboCah3o0/preview'),(33,33,'https://drive.google.com/file/d/1_kJSaXb0EoG9-VY9XqbEx4Fk0-KeWTmg/preview'),(34,34,'https://drive.google.com/file/d/1SGFcDGz6mortQIjftVUteyG7OdfqUqFQ/preview'),(35,35,'https://drive.google.com/file/d/1rPhhvhEilKzuOtkIu-U-SJLmBjpBv5xl/preview'),(36,36,'https://drive.google.com/file/d/1OF4v9-cOoCm1TThjKoPbCfls-jUp6LkF/preview'),(37,37,'https://drive.google.com/file/d/1peh9LCF63Qreb4L4jHdKMTaCUJbyWtnh/preview'),(38,38,'https://drive.google.com/file/d/10XyBwJnFnfL6JbTkWEc4oeo1aRPlIMCM/preview'),(39,39,'https://drive.google.com/file/d/1glAWn8_PsjXLqxrLjRXTdjiHYjpyt3qc/preview'),(40,40,'https://drive.google.com/file/d/1EXREvvZJPkvkuriJmxt5YvXkp3ImVE8L/preview'),(41,41,'https://drive.google.com/file/d/1inGFcPueOaXMSIRcunuoLFFoOd7gbWYZ/preview'),(42,42,'https://drive.google.com/file/d/1q2A8UvPF7CzyBviyHz1552NgmaL2tQWY/preview'),(43,43,'https://drive.google.com/file/d/197DiGufv2pENZ1YinqHxaryL0GEbJMKX/preview'),(44,44,'https://drive.google.com/file/d/1kukieMlBzuxOBwgTQkQB2CuMdKAS-dPd/preview'),(45,45,'https://drive.google.com/file/d/18LYz4-9DcR1pfRCghRMg-4845b0fFTBH/preview'),(46,46,'https://drive.google.com/file/d/1kukieMlBzuxOBwgTQkQB2CuMdKAS-dPd/preview'),(47,47,'https://drive.google.com/file/d/1A9m7JL5qpuTNsu7SJVI_cY8SW5tTVown/preview'),(48,48,'https://drive.google.com/file/d/1LjhihLlGyckKAn2oG2lGVCmDdTjAJ9Z8/preview'),(49,49,'https://drive.google.com/file/d/1RP2nE0mPnOLEaURHjrsFS0bu0xGT3Zhw/preview'),(50,50,'https://drive.google.com/file/d/1GFziPZe1Kaqa4J1QzwD2sU3KPwRGyOD9/preview');
/*!40000 ALTER TABLE `video` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-28  1:07:02
