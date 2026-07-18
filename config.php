<?php
// config.php - Database connection & full initialization
// ──────────────────────────────────────────────────────
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(86400);
if (session_status() === PHP_SESSION_NONE) session_start();

$db_host = 'localhost'; $db_port = '3306';
$db_name = 'imara_db';  $db_user = 'imara_user'; $db_pass = 'secure_password_change_me';

try {
    $pdo_init = new PDO("mysql:host=$db_host;port=$db_port", $db_user, $db_pass);
    $pdo_init->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_init->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

    function columnExists($pdo, $table, $column) {
        try { return $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$column'")->rowCount() > 0; }
        catch (Exception $e) { return false; }
    }

    // ── Tables ──────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        phone VARCHAR(50),
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(50) NOT NULL DEFAULT 'tenant',
        avatar_url TEXT,
        verified BOOLEAN NOT NULL DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS listings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        owner_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        type VARCHAR(50) NOT NULL,
        listing VARCHAR(50) NOT NULL,
        price INT NOT NULL,
        currency VARCHAR(10) NOT NULL DEFAULT 'RWF',
        period VARCHAR(50) NOT NULL DEFAULT 'night',
        beds INT NOT NULL DEFAULT 0,
        baths INT NOT NULL DEFAULT 0,
        sqm INT,
        amenities TEXT,
        images TEXT,
        district VARCHAR(100) NOT NULL,
        address TEXT,
        lat DOUBLE NOT NULL,
        lng DOUBLE NOT NULL,
        upi VARCHAR(50),
        verified BOOLEAN NOT NULL DEFAULT FALSE,
        available BOOLEAN NOT NULL DEFAULT TRUE,
        featured BOOLEAN NOT NULL DEFAULT FALSE,
        views INT NOT NULL DEFAULT 0,
        max_guests INT NOT NULL DEFAULT 2,
        cleaning_fee INT NOT NULL DEFAULT 0,
        service_fee INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        listing_id INT NOT NULL,
        tenant_id INT NOT NULL,
        landlord_id INT NOT NULL,
        visit_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        check_in DATE NULL, check_out DATE NULL,
        guests_count INT NOT NULL DEFAULT 1,
        total_price INT NOT NULL DEFAULT 0,
        status VARCHAR(50) NOT NULL DEFAULT 'pending',
        payment_method VARCHAR(50) NOT NULL DEFAULT 'momo',
        momo_phone VARCHAR(50),
        deposit INT NOT NULL DEFAULT 0,
        escrow_status VARCHAR(50) NOT NULL DEFAULT 'pending',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        from_id INT NOT NULL, to_id INT NOT NULL,
        listing_id INT, text TEXT NOT NULL,
        is_read BOOLEAN NOT NULL DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        listing_id INT NOT NULL, user_id INT NOT NULL,
        rating INT NOT NULL DEFAULT 5,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS wishlists (
        user_id INT NOT NULL, listing_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, listing_id)
    ) ENGINE=InnoDB");

    // ── Demo Users ──────────────────────────────────────
    $demoEmail = 'demo@imara.rw';
    $hasDemoUser = $pdo->query("SELECT COUNT(*) FROM users WHERE email='$demoEmail'")->fetchColumn() > 0;
    if (!$hasDemoUser) {
        $hp = password_hash('Demo1234!', PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO users (name,email,phone,password_hash,role,verified) VALUES (?,?,?,?,?,?)")
            ->execute(['Imara Demo Landlord',$demoEmail,'+250788000000',$hp,'landlord',1]);
    }
    $userId = $pdo->prepare("SELECT id FROM users WHERE email=?")->execute([$demoEmail]) ?
              $pdo->query("SELECT id FROM users WHERE email='$demoEmail'")->fetchColumn() : 1;

    $tenantEmail = 'tenant@imara.rw';
    $hasTenant = $pdo->query("SELECT COUNT(*) FROM users WHERE email='$tenantEmail'")->fetchColumn() > 0;
    if (!$hasTenant) {
        $hp = password_hash('Demo1234!', PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO users (name,email,phone,password_hash,role,verified) VALUES (?,?,?,?,?,?)")
            ->execute(['Amina Uwase',$tenantEmail,'+250788111111',$hp,'tenant',1]);
    }
    $tenantId = $pdo->query("SELECT id FROM users WHERE email='$tenantEmail'")->fetchColumn();

    // Seed extra landlords
    foreach ([
        ['Regis Nkurunziza','regis@imara.rw','+250788222333','landlord'],
        ['Solange Mukamana','solange@imara.rw','+250788444555','landlord'],
        ['Jean-Paul Habimana','jph@imara.rw','+250788666777','landlord'],
    ] as $lu) {
        if (!$pdo->query("SELECT COUNT(*) FROM users WHERE email='{$lu[1]}'")->fetchColumn()) {
            $hp = password_hash('Demo1234!',$_PASSWORD_BCRYPT ?? PASSWORD_BCRYPT);
            $pdo->prepare("INSERT INTO users (name,email,phone,password_hash,role,verified) VALUES (?,?,?,?,?,?)")
                ->execute([$lu[0],$lu[1],$lu[2],$hp,$lu[3],1]);
        }
    }
    $regisId   = $pdo->query("SELECT id FROM users WHERE email='regis@imara.rw'")->fetchColumn();
    $solangeId = $pdo->query("SELECT id FROM users WHERE email='solange@imara.rw'")->fetchColumn();
    $jphId     = $pdo->query("SELECT id FROM users WHERE email='jph@imara.rw'")->fetchColumn();

    // ── Check Seed Trigger ──────────────────────────────
    $listingCount = (int)$pdo->query("SELECT COUNT(*) FROM listings")->fetchColumn();
    $hasV2Seed    = (int)$pdo->query("SELECT COUNT(*) FROM listings WHERE title LIKE '%Kacyiru%' OR title LIKE '%Gishushu%'")->fetchColumn() > 0;

    if ($listingCount < 12 || !$hasV2Seed) {
        $pdo->exec("DELETE FROM listings; DELETE FROM reviews;");

        // ── Real Photo URLs (Unsplash, curated for Rwanda property types) ──
        // Each listing gets multiple comma-separated image URLs
        $PHOTO = [
            // Kiyovu luxury apartment
            'kiyovu' => implode(',', [
                'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1560185893-a55cbc8c57e8?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=800&q=80',
                '/images/prop_kiyovu.png',
            ]),
            // Nyarutarama villa
            'nyarutarama' => implode(',', [
                'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=800&q=80',
                '/images/prop_nyarutarama.png',
            ]),
            // Lake Kivu / Rubavu
            'rubavu' => implode(',', [
                'https://images.unsplash.com/photo-1506197603052-3cc9c3a201bd?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1582268611958-ebfd161ef9cf?auto=format&fit=crop&w=800&q=80',
                '/images/prop_rubavu.png',
            ]),
            // Musanze / volcano
            'musanze' => implode(',', [
                'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1449158743715-0a90ebb6d2d8?auto=format&fit=crop&w=800&q=80',
                '/images/prop_musanze.png',
            ]),
            // Kimihurura studio
            'studio' => implode(',', [
                'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&w=800&q=80',
            ]),
            // Suburban house
            'house' => implode(',', [
                'https://images.unsplash.com/photo-1568605114967-8130f3a36994?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1523217582562-09d0def993a6?auto=format&fit=crop&w=800&q=80',
            ]),
            // Modern apartment
            'apartment' => implode(',', [
                'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1484154218962-a197022b5858?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1493809842364-78817add7ffb?auto=format&fit=crop&w=800&q=80',
            ]),
            // Office / commercial
            'commercial' => implode(',', [
                'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1604328698692-f76ea9498e76?auto=format&fit=crop&w=800&q=80',
            ]),
            // Penthouse / luxury
            'penthouse' => implode(',', [
                'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1600607687644-c7171b42498b?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1570129477492-45c003edd2be?auto=format&fit=crop&w=800&q=80',
            ]),
            // Kayonza / eastern
            'eastern' => implode(',', [
                'https://images.unsplash.com/photo-1493397212122-2b85dda8106b?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1574362848149-11496d93a7c7?auto=format&fit=crop&w=800&q=80',
            ]),
            // Huye / academic
            'huye' => implode(',', [
                'https://images.unsplash.com/photo-1560185893-a55cbc8c57e8?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=800&q=80',
            ]),
            // Gishushu
            'gishushu' => implode(',', [
                '/images/prop_kiyovu_int.png',
                'https://images.unsplash.com/photo-1567767292278-a4f21aa2d36e?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1560185127-6a34a7b5b4a4?auto=format&fit=crop&w=800&q=80',
            ]),
            // Kacyiru
            'kacyiru' => implode(',', [
                'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1600047509782-20d39509f26d?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1507089947368-19c1da9775ae?auto=format&fit=crop&w=800&q=80',
            ]),
        ];

        $listings = [
            // ── 1. KIYOVU — Luxury Duplex Penthouse ──────────────────
            [
                'owner_id'=>$userId, 'title'=>'Luxury Penthouse — Kiyovu Embassy Zone',
                'description'=>"Live at the pinnacle of Kigali luxury in this breath-taking duplex penthouse situated in the prestigious Kiyovu Embassy Zone — home to ambassadors, government officials, and Kigali's elite.\n\nThe property sits on KN 31 St, a quiet tree-lined avenue with 24/7 police presence. Enjoy unobstructed 270 degree panoramic views of Kigali's rolling thousand hills from the private rooftop terrace. The open-plan living area features Italian marble floors, floor-to-ceiling glass walls, and a designer kitchen.\n\nLocation highlights:\n- 8 min walk to Kigali Convention Centre (KCC)\n- 4 min drive to Serena Hotel & CBD\n- Next to Belgian Embassy and ECOBANK HQ\n- 2 min to KN 5 Rd fine dining restaurants\n\nIncludes: automated biometric keyless entry, shared infinity pool, standby technician, 50Mbps fiber internet, and backup generator for 24/7 electricity.",
                'type'=>'apartment','listing'=>'rent','price'=>90000,'currency'=>'RWF','period'=>'night',
                'beds'=>3,'baths'=>2,'sqm'=>165,
                'amenities'=>'Infinity Pool,Fiber WiFi,Backup Generator,Biometric Lock,24h Security,Rooftop Terrace,Italian Kitchen,Parking x2,DSTV,AC All Rooms',
                'images'=>$PHOTO['penthouse'],
                'district'=>'Kiyovu','address'=>'KN 31 St, Kiyovu, Kigali City',
                'lat'=>-1.9500,'lng'=>30.0619,'upi'=>'1/03/09/02/1045',
                'verified'=>1,'featured'=>1,'max_guests'=>6,'cleaning_fee'=>18000,'service_fee'=>6000,
            ],
            // ── 2. NYARUTARAMA — Golf Course Villa ───────────────────
            [
                'owner_id'=>$userId, 'title'=>'Golf Course Estate Villa — Nyarutarama',
                'description'=>"This spectacular 5-bedroom estate occupies a prime 1,200m2 corner plot directly overlooking the Kigali Golf Course in Nyarutarama — Kigali's most prestigious residential enclave.\n\nThe villa is entirely enclosed within a 2.5m perimeter wall with electric fence, CCTV cameras, and a full-time gatekeeper. The sprawling grounds feature a 10m x 5m heated swimming pool, an outdoor barbecue pavilion with built-in pizza oven, and a beautifully lit tennis court.\n\nLocation highlights:\n- Directly adjacent to Kigali Golf Club (members' access available)\n- 4 min drive to Kimironko Market\n- 10 min to Kigali International Airport\n- Near RDB headquarters and diplomatic missions\n\nPerfect for: Executive company retreats, wedding celebrations, diplomatic residences, or family holidays. Fully staffed on request.",
                'type'=>'villa','listing'=>'rent','price'=>200000,'currency'=>'RWF','period'=>'night',
                'beds'=>5,'baths'=>4,'sqm'=>480,
                'amenities'=>'Heated Pool,Tennis Court,Golf Access,Full Security,Generator,Sat-WiFi,BBQ Pavilion,6-Car Garage,Chef on Request,Garden Staff',
                'images'=>$PHOTO['nyarutarama'],
                'district'=>'Nyarutarama','address'=>'KG 12 Ave, Nyarutarama, Gasabo District',
                'lat'=>-1.9438,'lng'=>30.0916,'upi'=>'1/03/12/03/981',
                'verified'=>1,'featured'=>1,'max_guests'=>12,'cleaning_fee'=>40000,'service_fee'=>15000,
            ],
            // ── 3. LAKE KIVU — Beach Cottage ─────────────────────────
            [
                'owner_id'=>$regisId, 'title'=>'Lakefront Beach Cottage — Lake Kivu, Rubavu',
                'description'=>"Immerse yourself in one of Africa's most breathtaking settings at this private beach cottage perched directly on the shores of Lake Kivu in Rubavu (Gisenyi).\n\nStep off the wooden deck straight onto the private sandy beach. Watch the sun set behind the Congolese mountains turning the still lake into liquid gold. The cottage features an open-plan tropical design with exposed wood beams, large sliding glass doors, and private mooring for kayaks.\n\nLocation highlights:\n- 50m direct beach access on Lake Kivu\n- 5 min walk to Rubavu town centre and restaurants\n- Views across to Goma, DRC\n- 2.5 hour scenic drive from Kigali (RN1)\n- Close to Gisenyi border crossing\n\nIncludes 2 single kayaks + 1 double kayak, outdoor fire pit with seating for 12, private outdoor shower, satellite WiFi, and nightly bonfire service.",
                'type'=>'house','listing'=>'rent','price'=>120000,'currency'=>'RWF','period'=>'night',
                'beds'=>4,'baths'=>3,'sqm'=>230,
                'amenities'=>'Private Beach,3 Kayaks,Bonfire Pit,Outdoor Shower,Sat-WiFi,Generator,Tropical Garden,Lake Views,Chef Optional,Sunrise Deck',
                'images'=>$PHOTO['rubavu'],
                'district'=>'Rubavu','address'=>'Lake Kivu Rd, Gisenyi Beach, Rubavu District',
                'lat'=>-1.6815,'lng'=>29.2588,'upi'=>'3/02/10/01/4502',
                'verified'=>1,'featured'=>1,'max_guests'=>8,'cleaning_fee'=>25000,'service_fee'=>9000,
            ],
            // ── 4. MUSANZE — Volcano Lodge ────────────────────────────
            [
                'owner_id'=>$regisId, 'title'=>'Virunga Volcano Panorama Lodge — Musanze',
                'description'=>"Wake up to the mist-shrouded silhouettes of the Virunga volcano range rising beyond your bedroom window in this architecturally stunning eco-lodge in Musanze (Ruhengeri).\n\nBuilt from local volcanic stone with a living green roof, the lodge is designed to blend seamlessly with Rwanda's Northern landscape. Inside you'll find a wood-burning fireplace, hand-carved furniture by local artisans, and organic cotton bedding.\n\nLocation highlights:\n- 40 min drive to Volcanoes National Park (Gorilla Tracking)\n- 15 min to Musanze town centre\n- Overlooking twin crater lakes Burera and Ruhondo\n- Near Dian Fossey Gorilla Fund research station\n\nThe lodge has on-call professional gorilla tracking guides, a fully equipped camping corner, and a communal organic kitchen garden from which all meals are sourced.",
                'type'=>'house','listing'=>'rent','price'=>95000,'currency'=>'RWF','period'=>'night',
                'beds'=>3,'baths'=>2,'sqm'=>175,
                'amenities'=>'Volcano Views,Fireplace,Organic Garden,Guide Service,Hot Showers,Eco Design,Parking,Solar Power,Stargazing Deck',
                'images'=>$PHOTO['musanze'],
                'district'=>'Musanze','address'=>'Kinigi Rd, near Volcanoes Park, Musanze District',
                'lat'=>-1.4996,'lng'=>29.6346,'upi'=>'2/01/05/04/392',
                'verified'=>1,'featured'=>1,'max_guests'=>6,'cleaning_fee'=>16000,'service_fee'=>7000,
            ],
            // ── 5. GISHUSHU — Modern Condo ────────────────────────────
            [
                'owner_id'=>$solangeId,'title'=>'Ultra-Modern 2-Bed Condo — Gishushu',
                'description'=>"A sleek contemporary 2-bedroom condo on the 9th floor of one of Gishushu's newest residential towers, featuring spectacular panoramic views of Kigali city and surrounding green hills.\n\nThe apartment has a smart home system (phone-controlled AC, lighting, and locks), a private balcony, high-speed fiber internet, and fully equipped European kitchen. Reserved underground parking included.\n\nLocation highlights:\n- 5 min drive to Kigali Heights shopping mall\n- Walking distance to KG 9 Ave restaurants\n- 10 min to Kigali International Airport\n- Next to Gishushu roundabout — excellent transport links\n- Near MTN, BK Arena, and tech startup district\n\nIdeal for: Business executives, digital nomads, couples, or small families.",
                'type'=>'apartment','listing'=>'rent','price'=>65000,'currency'=>'RWF','period'=>'night',
                'beds'=>2,'baths'=>2,'sqm'=>110,
                'amenities'=>'Smart Home,Panoramic Views,Fiber WiFi,Underground Parking,Gym Access,24h Security,European Kitchen,Balcony,AC,Rooftop Pool',
                'images'=>$PHOTO['gishushu'],
                'district'=>'Gishushu','address'=>'KG 9 Ave, Gishushu, Gasabo District',
                'lat'=>-1.9480,'lng'=>30.0860,'upi'=>'1/03/11/04/2847',
                'verified'=>1,'featured'=>1,'max_guests'=>4,'cleaning_fee'=>12000,'service_fee'=>4500,
            ],
            // ── 6. KACYIRU — Executive Townhouse ─────────────────────
            [
                'owner_id'=>$solangeId,'title'=>'Executive Government Quarter Townhouse — Kacyiru',
                'description'=>"This prestigious executive townhouse is located in Kacyiru — Kigali's government quarter and diplomatic hub. Walking distance to the Rwandan Parliament, Prime Minister's Office, and major government ministries.\n\nThe property features a formal dining room seating 10, a home office with dedicated fiber line, double garage, and a manicured garden enclosed within a 3m security wall with electric fence and CCTV.\n\nLocation highlights:\n- 2 min walk to Rwandan Parliament Building\n- 5 min to Ministry of Finance and National Bank\n- Next to Kacyiru bus terminal\n- 15 min to Kimironko Market\n- Walking distance to top restaurants on KG 7 Ave\n\nBest suited for: Diplomats, government officials, senior business executives, or long-term corporate lettings.",
                'type'=>'house','listing'=>'rent','price'=>80000,'currency'=>'RWF','period'=>'night',
                'beds'=>4,'baths'=>3,'sqm'=>280,
                'amenities'=>'Double Garage,Government Zone,CCTV,Electric Fence,Home Office,Fiber WiFi,Formal Dining,Garden,Security Guard,Generator',
                'images'=>$PHOTO['kacyiru'],
                'district'=>'Kacyiru','address'=>'KG 7 Ave, Kacyiru, Gasabo District',
                'lat'=>-1.9410,'lng'=>30.0770,'upi'=>'1/03/13/02/5621',
                'verified'=>1,'featured'=>0,'max_guests'=>8,'cleaning_fee'=>20000,'service_fee'=>8000,
            ],
            // ── 7. KIMIHURURA — Designer Studio ──────────────────────
            [
                'owner_id'=>$jphId,'title'=>'Imigongo Art Studio Loft — Kimihurura',
                'description'=>"A beautifully curated designer studio loft in Kimihurura — the heart of Kigali's cafe culture, art galleries, and boutique dining scene. The space is styled with authentic Rwandan Imigongo geometric art, hand-painted by local artists.\n\nThe studio features a built-in workspace perfect for remote workers, a kitchenette with espresso machine, and a private rooftop garden terrace with string lights.\n\nLocation highlights:\n- 2 min walk to Bourbon Coffee and Question Coffee\n- Walking distance to Papyrus Restaurant and Repub Lounge\n- 5 min to KG 9 Ave art galleries\n- Easy access to moto-taxis (motos) on every corner\n- 15 min to CHUK hospital\n\nPerfect for: Solo travelers, digital nomads, artists, business consultants.",
                'type'=>'studio','listing'=>'rent','price'=>42000,'currency'=>'RWF','period'=>'night',
                'beds'=>1,'baths'=>1,'sqm'=>52,
                'amenities'=>'Rooftop Garden,Workspace,Espresso Machine,Fiber WiFi,Smart TV,Art Collection,Kitchenette,Security,Moto Access,Cafe District',
                'images'=>$PHOTO['studio'],
                'district'=>'Kimihurura','address'=>'KG 28 Rd, Kimihurura, Gasabo District',
                'lat'=>-1.9528,'lng'=>30.0950,'upi'=>'1/03/08/01/294',
                'verified'=>1,'featured'=>0,'max_guests'=>2,'cleaning_fee'=>8000,'service_fee'=>3000,
            ],
            // ── 8. KIBAGABAGA — Family Townhouse FOR RENT ─────────────
            [
                'owner_id'=>$jphId,'title'=>'Spacious Family Compound — Kibagabaga',
                'description'=>"This generously proportioned 4-bedroom family compound in Kibagabaga offers the perfect balance of suburban tranquility and urban convenience. The property sits within a professionally managed gated estate with 24/7 roving security.\n\nThe home features a split-level design with a wraparound balcony, a dedicated children's play area, double garage, and a tropical garden.\n\nLocation highlights:\n- 10 min drive to Remera Bus Terminal\n- 5 min to Kibagabaga Catholic Hospital\n- Walking distance to multiple local supermarkets\n- Near King Faisal Hospital (5 min)\n- Quiet residential neighborhood — great for families\n\nAvailable for short stays, monthly, or yearly rental. Utilities (water, electricity, security fee) included in price.",
                'type'=>'house','listing'=>'rent','price'=>58000,'currency'=>'RWF','period'=>'night',
                'beds'=>4,'baths'=>3,'sqm'=>200,
                'amenities'=>"WiFi,Double Garage,Children's Play Area,Balcony,Washing Machine,Gated Estate,Security Guard,Generator,Hot Showers,DSTV",
                'images'=>$PHOTO['house'],
                'district'=>'Kibagabaga','address'=>'KG 302 St, Kibagabaga, Gasabo District',
                'lat'=>-1.9351,'lng'=>30.1235,'upi'=>'1/03/15/02/4921',
                'verified'=>1,'featured'=>0,'max_guests'=>10,'cleaning_fee'=>12000,'service_fee'=>4500,
            ],
            // ── 9. NYAMATA — FOR SALE — Investment Property ────────────
            [
                'owner_id'=>$userId,'title'=>'Prime Investment Plot + House — Nyamata FOR SALE',
                'description'=>"Rare investment opportunity in Nyamata, Bugesera District — one of Rwanda's fastest growing economic zones, 40km south of Kigali. This 1,800m2 corner plot with completed 3-bedroom house presents exceptional development potential.\n\nThe property sits 200m from the tarmac Kigali-Nyamata road and is within the designated Bugesera Special Economic Zone (SEZ). The land has clear RURA UPI title — fully transferable.\n\nInvestment highlights:\n- Bugesera Airport (BKT) is 12km away — international flights from 2025\n- SEZ incentives: 0% corporate tax for 7 years for developers\n- Land value appreciation: 300% in last 5 years\n- Close to Nyamata Genocide Memorial (tourist site)\n- Clear unencumbered title, immediately transferable\n\nSelling price: 45,000,000 RWF. Negotiable. Owner finance available.",
                'type'=>'house','listing'=>'sale','price'=>45000000,'currency'=>'RWF','period'=>'total',
                'beds'=>3,'baths'=>2,'sqm'=>1800,
                'amenities'=>'Clear UPI Title,Corner Plot,Water Connected,Electricity Grid,Road Frontage,SEZ Zone,Development Potential,Tarmac Access',
                'images'=>$PHOTO['eastern'],
                'district'=>'Nyamata','address'=>'Nyamata–Kigali Rd, Bugesera District',
                'lat'=>-2.1484,'lng'=>30.1001,'upi'=>'5/01/02/03/7721',
                'verified'=>1,'featured'=>1,'max_guests'=>6,'cleaning_fee'=>0,'service_fee'=>0,
            ],
            // ── 10. CBD — COMMERCIAL OFFICE FOR SALE ──────────────────
            [
                'owner_id'=>$userId,'title'=>'Prime CBD Office Space FOR SALE — City Tower, KN 4 St',
                'description'=>"A premium 280m2 commercial office unit on the 14th floor of Kigali's City Tower — the most recognizable address in Rwanda's Central Business District.\n\nThe space is fitted with raised access flooring, integrated LAN/fiber cabling, suspended ceilings with LED lighting, 8 executive glass-walled offices, a large open-plan team floor, and two premium meeting rooms.\n\nLocation highlights:\n- KN 4 St — the most prestigious commercial address in Rwanda\n- Shared reception lobby, security, and underground parking\n- Directly across from Kigali Convention Centre (KCC)\n- 3 min to MTN Rwanda HQ, BRD, and BK offices\n- Lift access, 24/7 security, CCTV, back-up power\n\nTitle: Sectional title with individual UPI number. Sale price: 280,000,000 RWF.",
                'type'=>'commercial','listing'=>'sale','price'=>280000000,'currency'=>'RWF','period'=>'total',
                'beds'=>0,'baths'=>4,'sqm'=>280,
                'amenities'=>'CBD Location,14th Floor,Glass Offices,Fiber Cabling,Underground Parking,Reception,Lift,Backup Power,CCTV,UPI Title',
                'images'=>$PHOTO['commercial'],
                'district'=>'Nyarugenge','address'=>'KN 4 St, City Tower, CBD, Kigali City',
                'lat'=>-1.9488,'lng'=>30.0607,'upi'=>'1/01/01/14/280',
                'verified'=>1,'featured'=>1,'max_guests'=>0,'cleaning_fee'=>0,'service_fee'=>0,
            ],
            // ── 11. REMERA — Modern 3-Bed Apartment ───────────────────
            [
                'owner_id'=>$regisId,'title'=>'Modern 3-Bed Apartment — Remera, Near Airport',
                'description'=>"Conveniently located in Remera, just 10 minutes from Kigali International Airport, this freshly renovated 3-bedroom apartment is ideal for business travelers or families on transit.\n\nFeatures a large open balcony with city views, modern fitted kitchen, fiber internet, and building-wide 24/7 generator. The building is managed by a professional property management company ensuring maintenance is prompt.\n\nLocation highlights:\n- 10 min drive to Kigali International Airport (KGL)\n- Walking distance to Remera Bus Terminal\n- Near Golf Club road bars and restaurants\n- Close to Chez Lando Hotel and Novotel\n- MTN MoMo agent on ground floor",
                'type'=>'apartment','listing'=>'rent','price'=>55000,'currency'=>'RWF','period'=>'night',
                'beds'=>3,'baths'=>2,'sqm'=>120,
                'amenities'=>'Airport Access,Fiber WiFi,Generator,Balcony,Parking,Security,Modern Kitchen,AC,DSTV,Hot Water',
                'images'=>$PHOTO['apartment'],
                'district'=>'Remera','address'=>'KK 17 St, Remera, Gasabo District',
                'lat'=>-1.9621,'lng'=>30.1147,'upi'=>'1/03/07/03/1893',
                'verified'=>1,'featured'=>0,'max_guests'=>6,'cleaning_fee'=>10000,'service_fee'=>4000,
            ],
            // ── 12. HUYE — University Town Apartment ──────────────────
            [
                'owner_id'=>$solangeId,'title'=>'University Quarter 2-Bed Flat — Huye (Butare)',
                'description'=>"Comfortable and affordable 2-bedroom apartment in Huye (formerly Butare), Rwanda's intellectual capital and university town. Located 200m from University of Rwanda's main campus gate.\n\nThe apartment is on the 3rd floor with balcony views of the hills. Includes furniture, water heater, and fiber internet. Very secure building — popular with professors, PhD students, NGO workers, and development professionals.\n\nLocation highlights:\n- 200m to University of Rwanda main gate\n- Next to Centre Iwacu cultural center\n- Walking distance to Huye Mountain Hotel\n- 5 min to Huye Market\n- 130km south of Kigali via RN1",
                'type'=>'apartment','listing'=>'rent','price'=>28000,'currency'=>'RWF','period'=>'night',
                'beds'=>2,'baths'=>1,'sqm'=>80,
                'amenities'=>'University Zone,Fiber WiFi,Hot Water,Balcony,Furnished,Security,Parking,DSTV',
                'images'=>$PHOTO['huye'],
                'district'=>'Huye','address'=>'Near University of Rwanda, Huye District',
                'lat'=>-2.5998,'lng'=>29.7395,'upi'=>'6/02/01/03/441',
                'verified'=>0,'featured'=>0,'max_guests'=>4,'cleaning_fee'=>6000,'service_fee'=>2500,
            ],
            // ── 13. KIGALI HEIGHTS — Luxury Apartment FOR SALE ────────
            [
                'owner_id'=>$jphId,'title'=>'Kigali Heights Luxury Apartment FOR SALE — KG 9 Ave',
                'description'=>"Own a piece of Kigali's most iconic mixed-use development — Kigali Heights. This never-lived-in 2-bedroom apartment on the 18th floor offers uninterrupted views across Kigali's skyline and beyond.\n\nThe apartment comes with European-standard finishes: Grohe bathroom fittings, Bosch kitchen appliances, engineered hardwood floors, and automated roller blinds. Access to the tower's swimming pool, gym, and concierge service is included.\n\nLocation highlights:\n- Kigali Heights is the most sought-after address in Rwanda\n- Walking distance to KN 5 Rd, Bourbon Coffee, and fine dining\n- 2 min to Belgian Embassy and ECOBANK\n- Ground floor has high-end retail (Java House, Nakumatt supermarket)\n- Resale value growth: 25% YoY in this development\n\nSelling price: 165,000,000 RWF. Bank mortgage available. Developer's warranty remaining.",
                'type'=>'apartment','listing'=>'sale','price'=>165000000,'currency'=>'RWF','period'=>'total',
                'beds'=>2,'baths'=>2,'sqm'=>115,
                'amenities'=>'18th Floor,Pool Access,Gym,Concierge,Grohe Fittings,Bosch Appliances,Hardwood Floors,2 Parking Bays,UPI Title,Brand New',
                'images'=>$PHOTO['penthouse'],
                'district'=>'Kiyovu','address'=>'KG 9 Ave, Kigali Heights Tower, Kigali City',
                'lat'=>-1.9510,'lng'=>30.0600,'upi'=>'1/01/02/18/115',
                'verified'=>1,'featured'=>1,'max_guests'=>4,'cleaning_fee'=>0,'service_fee'=>0,
            ],
            // ── 14. KARONGI — Kivu Belt Boutique Villa ────────────────
            [
                'owner_id'=>$userId,'title'=>'Boutique Hillside Villa — Karongi, Lake Kivu Belt',
                'description'=>"Hidden among eucalyptus-scented hillsides above Karongi (Kibuye) on Rwanda's Lake Kivu belt, this boutique villa is an extraordinary escape from city life.\n\nThe villa sits 180m above lake level, offering absolutely extraordinary panoramic views across Lake Kivu's many inlets and islands. Built in 2022 with local stone and tropical hardwood, it is both architecturally stunning and deeply comfortable.\n\nLocation highlights:\n- Karongi is the 'hidden gem' of Rwanda tourism\n- Boat trips to Amahoro Island and Napoleon Island available locally\n- Inyange Boat Club 15 min drive\n- 2.5 hours from Kigali via scenic lake route\n- Close to Kibuye historical memorials\n\nIncludes: private sunset viewing terrace, outdoor dining area with BBQ, hammock garden, and boat trip arrangements.",
                'type'=>'villa','listing'=>'rent','price'=>145000,'currency'=>'RWF','period'=>'night',
                'beds'=>4,'baths'=>3,'sqm'=>290,
                'amenities'=>'Lake Kivu Views,Sunset Terrace,Boat Trips,BBQ,Hammock Garden,Stone Architecture,Hot Showers,Solar Power,Sat-WiFi,Tropical Garden',
                'images'=>$PHOTO['rubavu'],
                'district'=>'Karongi','address'=>'Hillside Rd, Karongi District, Western Province',
                'lat'=>-2.0633,'lng'=>29.3460,'upi'=>'4/01/03/02/815',
                'verified'=>1,'featured'=>1,'max_guests'=>8,'cleaning_fee'=>28000,'service_fee'=>11000,
            ],
            // ── 15. KIMIRONKO — FOR SALE — Plot ──────────────────────
            [
                'owner_id'=>$regisId,'title'=>'Fully Serviced Plot FOR SALE — Kimironko',
                'description'=>"A rare fully serviced residential plot of 800m2 in the heart of Kimironko — one of Kigali's most dynamic and fast-growing neighborhoods.\n\nThe plot is level, fully cleared, and already connected to: city water (WASAC), electricity (REG), and a 30cm drainage canal on the boundary. No demolition costs — ready to build immediately.\n\nLand highlights:\n- 800m2 with 20m street frontage on paved road\n- Walking distance to Kimironko Market (Rwanda's largest open market)\n- 5 min to Kicukiro College and Remera Bus Terminal\n- High rental demand — ideal for residential or mixed-use development\n- RURA UPI land certificate in hand\n\nAsk price: 85,000,000 RWF. Serious buyers only.",
                'type'=>'house','listing'=>'sale','price'=>85000000,'currency'=>'RWF','period'=>'total',
                'beds'=>0,'baths'=>0,'sqm'=>800,
                'amenities'=>'Serviced Plot,Water Connected,Electricity Grid,Drainage,Level Site,Road Frontage,Clear UPI,Immediate Build',
                'images'=>$PHOTO['house'],
                'district'=>'Kimironko','address'=>'KG 11 Ave, Kimironko, Gasabo District',
                'lat'=>-1.9285,'lng'=>30.1064,'upi'=>'1/03/06/01/3047',
                'verified'=>1,'featured'=>0,'max_guests'=>0,'cleaning_fee'=>0,'service_fee'=>0,
            ],
        ];

        $ins = $pdo->prepare("
            INSERT INTO listings (owner_id,title,description,type,listing,price,currency,period,beds,baths,sqm,amenities,images,district,address,lat,lng,upi,verified,featured,max_guests,cleaning_fee,service_fee)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        $reviewData = [
            'Luxury Penthouse — Kiyovu Embassy Zone' => [
                [5, "Absolutely the best place I have ever stayed in Kigali. Rooftop views are insane — you can see the entire city. The biometric lock was cool and made me feel super secure."],
                [5, "We hosted a small dinner party on the rooftop terrace and it was magical. The pool is gorgeous. 100% recommended for anyone visiting Kigali in style."],
                [4, "Stunning apartment, very clean and modern. WiFi is fast. Only minor issue was the elevator was slow on busy evenings, but that's a building thing not the apartment."],
            ],
            'Golf Course Estate Villa — Nyarutarama' => [
                [5, "Hosted our company's annual executive retreat here. 18 of us — the villa handled it perfectly. The tennis court and BBQ pavilion were the highlights."],
                [5, "Perfect for our family holiday. Kids loved the pool, adults loved the garden. The golf course views from the breakfast veranda are stunning. Will definitely return."],
            ],
            'Lakefront Beach Cottage — Lake Kivu, Rubavu' => [
                [5, "Words can't describe how beautiful this place is. Woke up at 5am to kayak as the sun rose over Congo — one of the most magical moments of my life."],
                [5, "Private beach is real! No noise, no crowds — just us, the lake, and the mountains. The fire pit at night was incredible."],
                [4, "Excellent property. The kayaks are in great condition. Only thing — bring mosquito repellent for the evenings. Otherwise perfect."],
            ],
            'Virunga Volcano Panorama Lodge — Musanze' => [
                [5, "The volcanoes were visible from bed — I am not exaggerating! We used the guide service for gorilla tracking and it was the experience of a lifetime."],
                [5, "Cozy, authentic, and absolutely stunning. The fireplace + volcanic stone design is incredible. Fresh garden vegetables cooked for breakfast daily."],
            ],
            'Ultra-Modern 2-Bed Condo — Gishushu' => [
                [5, "Smart home controls were amazing — AC, lights, all from my phone. City views from bed were gorgeous, especially at night. 10/10."],
                [4, "Great apartment, very clean. The rooftop pool is shared but it's always empty early morning. Gym is well-equipped. Very convenient location."],
            ],
            'Imigongo Art Studio Loft — Kimihurura' => [
                [5, "Perfect solo traveler spot. I worked remotely for 2 weeks from here — the workspace and fiber WiFi were great. Loved walking to Bourbon Coffee each morning."],
                [4, "Beautifully designed with real Rwandan art. Very Instagram-worthy. The host was very responsive. Small but very efficient layout."],
            ],
            'Boutique Hillside Villa — Karongi, Lake Kivu Belt' => [
                [5, "The view from the terrace at sunset reduced me to tears. Lake Kivu stretching to the horizon, complete silence, and the best Rwandan food I have ever eaten."],
                [5, "Hidden gem! So few tourists know about Karongi. This villa is the perfect base. We did boat trips to Napoleon Island — unforgettable."],
            ],
        ];

        foreach ($listings as $d) {
            $ins->execute([
                $d['owner_id'],$d['title'],$d['description'],$d['type'],$d['listing'],
                $d['price'],$d['currency'],$d['period'],$d['beds'],$d['baths'],$d['sqm'],
                $d['amenities'],$d['images'],$d['district'],$d['address'],
                $d['lat'],$d['lng'],$d['upi'],$d['verified'],$d['featured'],
                $d['max_guests'],$d['cleaning_fee'],$d['service_fee']
            ]);
            $lid = (int)$pdo->lastInsertId();

            if (isset($reviewData[$d['title']])) {
                $rins = $pdo->prepare("INSERT INTO reviews (listing_id,user_id,rating,comment) VALUES (?,?,?,?)");
                foreach ($reviewData[$d['title']] as [$rating, $comment]) {
                    $rins->execute([$lid, $tenantId, $rating, $comment]);
                }
            }
        }
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
