// Location data for cascading country/state/city dropdowns
const locationData = {
  India: {
    "Andhra Pradesh": ["Visakhapatnam", "Vijayawada", "Guntur", "Tirupati", "Kurnool"],
    "Arunachal Pradesh": ["Itanagar", "Tawang", "Pasighat", "Ziro", "Bomdila"],
    "Assam": ["Guwahati", "Dibrugarh", "Silchar", "Jorhat", "Tezpur"],
    "Bihar": ["Patna", "Gaya", "Muzaffarpur", "Bhagalpur", "Darbhanga"],
    "Chhattisgarh": ["Raipur", "Bilaspur", "Durg", "Korba", "Jagdalpur"],
    "Goa": ["Panaji", "Margao", "Vasco da Gama", "Mapusa", "Ponda"],
    "Gujarat": ["Ahmedabad", "Surat", "Vadodara", "Rajkot", "Bhavnagar", "Jamnagar", "Gandhinagar", "Junagadh", "Anand", "Bharuch"],
    "Haryana": ["Gurugram", "Faridabad", "Panipat", "Hisar", "Ambala"],
    "Himachal Pradesh": ["Shimla", "Dharamshala", "Solan", "Mandi", "Kullu"],
    "Jharkhand": ["Ranchi", "Jamshedpur", "Dhanbad", "Bokaro", "Deoghar"],
    "Karnataka": ["Bengaluru", "Mysuru", "Hubballi", "Mangaluru", "Belagavi"],
    "Kerala": ["Thiruvananthapuram", "Kochi", "Kozhikode", "Thrissur", "Kollam"],
    "Madhya Pradesh": ["Bhopal", "Indore", "Jabalpur", "Gwalior", "Ujjain"],
    "Maharashtra": ["Mumbai", "Pune", "Nagpur", "Nashik", "Aurangabad", "Kolhapur", "Solapur", "Amravati", "Thane", "Navi Mumbai"],
    "Manipur": ["Imphal", "Thoubal", "Bishnupur", "Ukhrul", "Churachandpur"],
    "Meghalaya": ["Shillong", "Tura", "Jowai", "Nongstoin", "Baghmara"],
    "Mizoram": ["Aizawl", "Lunglei", "Champhai", "Serchhip", "Kolasib"],
    "Nagaland": ["Kohima", "Dimapur", "Mokokchung", "Tuensang", "Wokha"],
    "Odisha": ["Bhubaneswar", "Cuttack", "Rourkela", "Sambalpur", "Puri"],
    "Punjab": ["Ludhiana", "Amritsar", "Jalandhar", "Patiala", "Bathinda"],
    "Rajasthan": ["Jaipur", "Jodhpur", "Udaipur", "Kota", "Ajmer", "Bikaner", "Alwar", "Bharatpur"],
    "Sikkim": ["Gangtok", "Namchi", "Gyalshing", "Mangan", "Rangpo"],
    "Tamil Nadu": ["Chennai", "Coimbatore", "Madurai", "Salem", "Tiruchirappalli"],
    "Telangana": ["Hyderabad", "Warangal", "Karimnagar", "Nizamabad", "Khammam"],
    "Tripura": ["Agartala", "Udaipur", "Dharmanagar", "Belonia", "Kailashahar"],
    "Uttar Pradesh": ["Lucknow", "Kanpur", "Agra", "Varanasi", "Prayagraj", "Meerut", "Noida", "Ghaziabad", "Bareilly", "Aligarh"],
    "Uttarakhand": ["Dehradun", "Haridwar", "Haldwani", "Roorkee", "Rishikesh"],
    "West Bengal": ["Kolkata", "Howrah", "Durgapur", "Siliguri", "Asansol"],

    // Union Territories
    "Andaman and Nicobar Islands": ["Port Blair"],
    "Chandigarh": ["Chandigarh"],
    "Dadra and Nagar Haveli and Daman and Diu": ["Daman", "Diu", "Silvassa"],
    "Delhi": ["New Delhi", "Dwarka", "Rohini", "Karol Bagh", "Saket"],
    "Jammu and Kashmir": ["Srinagar", "Jammu", "Anantnag", "Baramulla", "Pulwama"],
    "Ladakh": ["Leh", "Kargil"],
    "Lakshadweep": ["Kavaratti"],
    "Puducherry": ["Puducherry", "Karaikal", "Mahe", "Yanam"]
  },
  USA: {
    California: ['Los Angeles', 'San Francisco', 'San Diego'],
    Texas: ['Houston', 'Austin', 'Dallas'],
    'New York': ['New York City', 'Buffalo', 'Albany']
  },
  UK: {
    England: ['London', 'Manchester', 'Birmingham'],
    Scotland: ['Edinburgh', 'Glasgow'],
    Wales: ['Cardiff', 'Swansea']
  },
  Afghanistan: {
    Kabul: ['Kabul', 'Herat', 'Mazar-e-Sharif', 'Kandahar', 'Jalalabad'],
    'Helmand Province': ['Lashkar Gah', 'Gereshk', 'Nahri Saraj', 'Musa Qala', 'Sangin'],
    'Nangarhar Province': ['Jalalabad', 'Shinwar', 'Mohmand Dara', 'Khogyani', 'Behsud']
  },
  Albania: {
    Tirana: ['Tirana', 'Durrës', 'Vlorë', 'Elbasan', 'Shkodër'],
    Fier: ['Fier', 'Patos', 'Ballsh', 'Roskovec', 'Lushnjë'],
    Berat: ['Berat', 'Kuçovë', 'Polican', 'Ura Vajgurore', 'Gramsh']
  },
  Algeria: {
    Algiers: ['Algiers', 'Boumerdas', 'Blida', 'Tipaza', 'Kolea'],
    Oran: ['Oran', 'Es Senia', 'Bir El Djir', 'Misserghin', 'Arzew'],
    Constantine: ['Constantine', 'Khroub', 'Zighoud Youcef', 'Ain Abid', 'Hamma Bouziane']
  },
  Argentina: {
    'Buenos Aires': ['Buenos Aires', 'La Plata', 'Mar del Plata', 'Quilmes', 'Lomas de Zamora'],
    Córdoba: ['Córdoba', 'Villa María', 'Río Cuarto', 'Carlos Paz', 'San Francisco'],
    Mendoza: ['Mendoza', 'Godoy Cruz', 'Luján de Cuyo', 'San Rafael', 'General Alvear']
  },
  Australia: {
    'New South Wales': ['Sydney', 'Newcastle', 'Wollongong', 'Broken Hill', 'Coffs Harbour'],
    Victoria: ['Melbourne', 'Geelong', 'Ballarat', 'Bendigo', 'Shepparton'],
    Queensland: ['Brisbane', 'Gold Coast', 'Cairns', 'Townsville', 'Sunshine Coast']
  },
  Austria: {
    Vienna: ['Vienna'],
    'Lower Austria': ['St. Pölten', 'Wiener Neustadt', 'Krems', 'Melk', 'Neunkirchen'],
    'Upper Austria': ['Linz', 'Wels', 'Steyr', 'Bruck an der Donau', 'Vöcklabruck']
  },
  Bangladesh: {
    Dhaka: ['Dhaka', 'Narayanganj', 'Gazipur', 'Tongi', 'Savar'],
    Chittagong: ['Chittagong', 'Cox\'s Bazar', 'Comilla', 'Noakhali', 'Feni'],
    Sylhet: ['Sylhet', 'Moulvibazar', 'Habiganj', 'Sunamganj', 'Shahjalal']
  },
  Belgium: {
    Flanders: ['Antwerp', 'Ghent', 'Bruges', 'Leuven', 'Mechelen'],
    Wallonia: ['Charleroi', 'Liège', 'Namur', 'Mons', 'La Louvière'],
    'Brussels-Capital Region': ['Brussels']
  },
  Brazil: {
    'São Paulo': ['São Paulo', 'Campinas', 'Santos', 'São Bernardo do Campo', 'Guarulhos'],
    'Rio de Janeiro': ['Rio de Janeiro', 'Niterói', 'Duque de Caxias', 'Nova Iguaçu', 'Belford Roxo'],
    'Minas Gerais': ['Belo Horizonte', 'Uberlândia', 'Contagem', 'Juiz de Fora', 'Betim']
  },
  Cambodia: {
    Phnom_Penh: ['Phnom Penh', 'Ta Khmau', 'Saang', 'Kandal', 'Chbar Ampov'],
    Siem_Reap: ['Siem Reap', 'Angkor Thom', 'Sotr Nikum', 'Prasat Bakong', 'Banteay Srei'],
    Battambang: ['Battambang', 'Sangkae', 'Banang', 'Thmar Kol', 'Rotanak Mondol']
  },
  Canada: {
    Ontario: ['Toronto', 'Ottawa', 'Mississauga', 'Brampton', 'Hamilton'],
    'British Columbia': ['Vancouver', 'Surrey', 'Burnaby', 'Richmond', 'Victoria'],
    Alberta: ['Calgary', 'Edmonton', 'Red Deer', 'Lethbridge', 'St. Albert']
  },
  Chile: {
    'Santiago Metropolitan': ['Santiago', 'Puente Alto', 'La Florida', 'Maipú', 'San Bernardo'],
    'Valparaíso': ['Valparaíso', 'Viña del Mar', 'Quilpué', 'Villa Alemana', 'Quillota'],
    'Biobío': ['Concepción', 'Talcahuano', 'Los Ángeles', 'Chillán', 'Coronel']
  },
  China: {
    Guangdong: ['Guangzhou', 'Shenzhen', 'Foshan', 'Dongguan', 'Zhongshan'],
    'Zhejiang Province': ['Hangzhou', 'Ningbo', 'Wenzhou', 'Shaoxing', 'Jiaxing'],
    'Jiangsu Province': ['Nanjing', 'Suzhou', 'Wuxi', 'Changzhou', 'Nantong']
  },
  Colombia: {
    Bogotá: ['Bogotá', 'Soacha', 'Chía', 'La Calera', 'Girardot'],
    Antioquia: ['Medellín', 'Bello', 'Envigado', 'Itagüí', 'Apartadó'],
    'Valle del Cauca': ['Cali', 'Buenaventura', 'Palmira', 'Tuluá', 'Cartago']
  },
  Denmark: {
    'Capital Region': ['Copenhagen', 'Frederiksberg', 'Helsingør', 'Hillerød', 'Lyngby-Taarbæk'],
    'Midtjylland': ['Aarhus', 'Herning', 'Silkeborg', 'Randers', 'Viborg'],
    'Nordjylland': ['Aalborg', 'Hjørring', 'Frederikshavn', 'Thisted', 'Brønderslev']
  },
  Egypt: {
    'Cairo Governorate': ['Cairo', 'Giza', 'Shubra El-Kheima', 'Port Said', 'Suez'],
    'Alexandria Governorate': ['Alexandria', 'El Mahalla El Kubra', 'Kafr El Sheikh', 'Damietta', 'Mansoura'],
    'Giza Governorate': ['Giza', 'Cairo', '6th October City', 'Sheikh Zayed', 'Al Badrashein']
  },
  Ethiopia: {
    'Addis Ababa': ['Addis Ababa', 'Adama', 'Dire Dawa', 'Mekele', 'Bahir Dar'],
    'Oromia': ['Adama', 'Jimma', 'Nekemte', 'Assela', 'Shashemene'],
    'Amhara': ['Bahir Dar', 'Gondar', 'Debre Markos', 'Desse', 'Debre Birhan']
  },
  Finland: {
    Uusimaa: ['Helsinki', 'Espoo', 'Vantaa', 'Porvoo', 'Hyvinkää'],
    'Pirkanmaa': ['Tampere', 'Nokia', 'Valkeakoski', 'Mänttä-Vilppula', 'Ruovesi'],
    'Varsinais-Suomi': ['Turku', 'Kaarina', 'Raisio', 'Salon', 'Paimio']
  },
  France: {
    'Île-de-France': ['Paris', 'Versailles', 'Boulogne-Billancourt', 'Saint-Denis', 'Argenteuil'],
    'Auvergne-Rhône-Alpes': ['Lyon', 'Grenoble', 'Saint-Étienne', 'Clermont-Ferrand', 'Annecy'],
    'Occitanie': ['Toulouse', 'Montpellier', 'Nîmes', 'Perpignan', 'Carcassonne']
  },
  Germany: {
    'North Rhine-Westphalia': ['Cologne', 'Düsseldorf', 'Dortmund', 'Essen', 'Duissern'],
    Bavaria: ['Munich', 'Nuremberg', 'Augsburg', 'Regensburg', 'Ingolstadt'],
    'Baden-Württemberg': ['Stuttgart', 'Karlsruhe', 'Freiburg im Breisgau', 'Mannheim', 'Heidelberg']
  },
  Greece: {
    'Attica': ['Athens', 'Piraeus', 'Glyfada', 'Marousi', 'Kalamaria'],
    'Central Macedonia': ['Thessaloniki', 'Kalamaria', 'Polygyros', 'Katerini', 'Veria'],
    Crete: ['Heraklion', 'Chania', 'Rethymno', 'Ierapetra', 'Agios Nikolaos']
  },
  Hungary: {
    'Central Hungary': ['Budapest', 'Debrecen', 'Szeged', 'Miskolc', 'Pécs'],
    'Western Transdanubia': ['Győr', 'Szombathely', 'Veszprém', 'Kaposvár', 'Sopron'],
    'Northern Great Plain': ['Debrecen', 'Nyíregyháza', 'Szolnok', 'Békéscsaba', 'Hódmezővásárhely']
  },
  Indonesia: {
    'DKI Jakarta': ['Jakarta', 'Bekasi', 'Depok', 'Tangerang', 'South Tangerang'],
    Jawa_Barat: ['Bandung', 'Bekasi', 'Bogor', 'Cirebon', 'Tasikmalaya'],
    Jawa_Timur: ['Surabaya', 'Malang', 'Kediri', 'Surakarta', 'Madiun']
  },
  Iran: {
    'Tehran Province': ['Tehran', 'Mashhad', 'Isfahan', 'Karaj', 'Tabriz'],
    'Isfahan Province': ['Isfahan', 'Tehran', 'Kashan', 'Najafabad', 'Qom'],
    'Fars Province': ['Shiraz', 'Tehran', 'Persepolis', 'Kazerun', 'Eqlid']
  },
  Iraq: {
    'Baghdad Governorate': ['Baghdad', 'Basra', 'Mosul', 'Erbil', 'Sulaymaniyah'],
    'Najaf Governorate': ['Najaf', 'Karbala', 'Al Kufah', 'Al Hindiyah', 'Al-Mishkhab'],
    'Basra Governorate': ['Basra', 'Baghdad', 'Al Zubair', 'Al-Madinah', 'Fao']
  },
  Ireland: {
    Leinster: ['Dublin', 'Belfast', 'Cork', 'Limerick', 'Waterford'],
    Munster: ['Cork', 'Limerick', 'Waterford', 'Kerry', 'Tipperary'],
    Connacht: ['Galway', 'Sligo', 'Letterkenny', 'Castlebar', 'Westport']
  },
  Israel: {
    Jerusalem: ['Jerusalem', 'Tel Aviv', 'Haifa', 'Beersheba', 'Nazareth'],
    'Northern District': ['Nazareth', 'Safed', 'Kiryat Shmona', 'Tiberias', 'Acre'],
    'Haifa District': ['Haifa', 'Nahariya', 'Kiryat Motzkin', 'Atlit', 'Zikhron Ya\'akov']
  },
  Italy: {
    Lombardy: ['Milan', 'Bergamo', 'Brescia', 'Monza', 'Como'],
    Lazio: ['Rome', 'Fiumicino', 'Guidonia Montecelio', 'Aprilia', 'Latina'],
    Campania: ['Naples', 'Salerno', 'Torre del Greco', 'Casoria', 'Giugliano in Campania']
  },
  Japan: {
    Tokyo: ['Tokyo', 'Yokohama', 'Osaka', 'Nagoya', 'Sapporo'],
    'Osaka Prefecture': ['Osaka', 'Kobe', 'Kyoto', 'Sakai', 'Hirakata'],
    'Kanagawa Prefecture': ['Yokohama', 'Kawasaki', 'Sagamihara', 'Fujisawa', 'Odawara']
  },
  Kenya: {
    Nairobi: ['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret'],
    Kiambu: ['Kiambu', 'Thika', 'Ruiru', 'Kikuyu', 'Limuru'],
    Nakuru: ['Nakuru', 'Naivasha', 'Gilgil', 'Molo', 'Subukia']
  },
  Malaysia: {
    Selangor: ['Shah Alam', 'Petaling Jaya', 'Klang', 'Kuala Lumpur', 'Putrajaya'],
    Johor: ['Johor Bahru', 'Malacca', 'Muar', 'Batu Pahat', 'Kluang'],
    Sabah: ['Kota Kinabalu', 'Sandakan', 'Tawau', 'Lahad Datu', 'Semporna']
  },
  Mexico: {
    'Mexico City': ['Mexico City', 'Guadalajara', 'Monterrey', 'Puebla', 'Tijuana'],
    Jalisco: ['Guadalajara', 'Zapopan', 'Tlaquepaque', 'Tonalá', 'Chapala'],
    'Nuevo León': ['Monterrey', 'San Nicolás de los Garza', 'Apodaca', 'Guadalupe', 'Escobedo']
  },
  Morocco: {
    'Casablanca-Settat': ['Casablanca', 'Rabat', 'Marrakech', 'Fes', 'Tangier'],
    'Marrakech-Safi': ['Marrakech', 'Essaouira', 'Safi', 'Chichaoua', 'El Kelâa Des Sraghna'],
    'Fès-Meknès': ['Fes', 'Meknes', 'Sefrou', 'El Hajeb', 'Tlata']
  },
  Nepal: {
    'Bagmati Province': ['Kathmandu', 'Pokhara', 'Lalitpur', 'Bhaktapur', 'Birgunj'],
    'Province No. 1': ['Biratnagar', 'Dharan', 'Itahari', 'Rangeli', 'Dhankuta'],
    'Gandaki Province': ['Pokhara', 'Lumbini', 'Baglung', 'Syangja', 'Lamjung']
  },
  Netherlands: {
    'North Holland': ['Amsterdam', 'Rotterdam', 'The Hague', 'Utrecht', 'Eindhoven'],
    'South Holland': ['The Hague', 'Rotterdam', 'Dordrecht', 'Leiden', 'Zaandam'],
    'North Brabant': ['Eindhoven', 'Tilburg', 'Breda', 's-Hertogenbosch', 'Nijmegen']
  },
  'New Zealand': {
    Auckland: ['Auckland', 'Hamilton', 'Tauranga', 'Napier', 'Hastings'],
    Canterbury: ['Christchurch', 'Dunedin', 'Nelson', 'Blenheim', 'Greymouth'],
    Wellington: ['Wellington', 'Nelson', 'Palmerston North', 'Paraparaumu', 'Levin']
  },
  Nigeria: {
    Lagos: ['Lagos', 'Ibadan', 'Kano', 'Kaduna', 'Port Harcourt'],
    'Kano State': ['Kano', 'Katsina', 'Zaria', 'Maiduguri', 'Sokoto'],
    'Rivers State': ['Port Harcourt', 'Bonny', 'Degema', 'Omoku', 'Ahoada']
  },
  Norway: {
    Oslo: ['Oslo', 'Bergen', 'Trondheim', 'Stavanger', 'Drammen'],
    'Vestland': ['Bergen', 'Stavanger', 'Haugesund', 'Sogndal', 'Førde'],
    'Viken': ['Oslo', 'Drammen', 'Fredrikstad', 'Sarpsborg', 'Moss']
  },
  Pakistan: {
    Punjab: ['Lahore', 'Faisalabad', 'Rawalpindi', 'Gujranwala', 'Sialkot'],
    Sindh: ['Karachi', 'Hyderabad', 'Sukkur', 'Larkana', 'Mirpur Khas'],
    'Khyber Pakhtunkhwa': ['Peshawar', 'Mardan', 'Abbottabad', 'Swat', 'Kohat']
  },
  Peru: {
    Lima: ['Lima', 'Arequipa', 'Trujillo', 'Chiclayo', 'Piura'],
    'La Libertad': ['Trujillo', 'Chiclayo', 'Trujillo', 'Huamachuco', 'Otuzco'],
    'Arequipa': ['Arequipa', 'Cayma', 'Yanahuara', 'Cerro Verde', 'Pueblo Libre']
  },
  Philippines: {
    'National Capital Region': ['Quezon City', 'Manila', 'Caloocan', 'Pasig', 'Taguig'],
    'Central Luzon': ['Angeles', 'Clark', 'San Fernando', 'Balanga', 'Cabanatuan'],
    'Central Visayas': ['Cebu City', 'Mandaue', 'Lapu-Lapu', 'Toledo', 'Danao']
  },
  Poland: {
    'Masovian Voivodeship': ['Warsaw', 'Kraków', 'Łódź', 'Wrocław', 'Poznań'],
    'Silesian Voivodeship': ['Katowice', 'Gliwice', 'Częstochowa', 'Bielsko-Biała', 'Sosnowiec'],
    'Lesser Poland Voivodeship': ['Kraków', 'Tarnów', 'Nowy Sącz', 'Oświęcim', 'Zakopane']
  },
  Portugal: {
    'Lisbon District': ['Lisbon', 'Setúbal', 'Amadora', 'Agualva-Cacém', 'Almada'],
    'Porto District': ['Porto', 'Vila Nova de Gaia', 'Gondomar', 'Valongo', 'Paredes'],
    'Braga District': ['Braga', 'Guimarães', 'Barcelos', 'Póvoa de Varzim', 'Viana do Castelo']
  },
  Romania: {
    Bucuresti: ['Bucharest', 'Cluj-Napoca', 'Timișoara', 'Iași', 'Constanța'],
    'Cluj County': ['Cluj-Napoca', 'Turda', 'Dej', 'Huedin', 'Câmpia Turzii'],
    'Timiș County': ['Timișoara', 'Lugoj', 'Caransebeș', 'Făget', 'Recaș']
  },
  Russia: {
    'Moscow Federal City': ['Moscow', 'Saint Petersburg', 'Novosibirsk', 'Yekaterinburg', 'Nizhny Novgorod'],
    'Saint Petersburg Federal City': ['Saint Petersburg', 'Moscow', 'Novosibirsk', 'Yekaterinburg', 'Vladivostok'],
    'Novosibirsk Oblast': ['Novosibirsk', 'Omsk', 'Krasnoyarsk', 'Barnaul', 'Tomsk']
  },
  'Saudi Arabia': {
    'Riyadh Province': ['Riyadh', 'Jeddah', 'Mecca', 'Medina', 'Dammam'],
    'Makkah Province': ['Jeddah', 'Mecca', 'Taif', 'Bahra', 'Al Jumum'],
    'Eastern Province': ['Dammam', 'Al Khobar', 'Dhahran', 'Qatif', 'Hafar Al Batin']
  },
  Singapore: {
    'Central Region': ['Singapore'],
    'North Region': ['Singapore'],
    'East Region': ['Singapore']
  },
  'South Africa': {
    Gauteng: ['Johannesburg', 'Pretoria', 'Durban', 'Cape Town', 'Port Elizabeth'],
    'Western Cape': ['Cape Town', 'Stellenbosch', 'Paarl', 'George', 'Mossel Bay'],
    'KwaZulu-Natal': ['Durban', 'Pietermaritzburg', 'Ulundi', 'Richards Bay', 'Newcastle']
  },
  'South Korea': {
    'Seoul Special City': ['Seoul', 'Busan', 'Incheon', 'Daegu', 'Daejeon'],
    'Busan Metropolitan City': ['Busan', 'Seoul', 'Incheon', 'Daegu', 'Ulsan'],
    'Gyeonggi Province': ['Suwon', 'Seoul', 'Anyang', 'Bucheon', 'Seongnam']
  },
  Spain: {
    'Community of Madrid': ['Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Zaragoza'],
    Catalonia: ['Barcelona', 'Madrid', 'Valencia', 'Sevilla', 'Zaragoza'],
    'Andalusia': ['Sevilla', 'Malaga', 'Cordoba', 'Granada', 'Murcia']
  },
  'Sri Lanka': {
    'Western Province': ['Colombo', 'Kandy', 'Galle', 'Jaffna', 'Negombo'],
    'Central Province': ['Kandy', 'Colombo', 'Nuwara Eliya', 'Matale', 'Gampola'],
    'Southern Province': ['Galle', 'Colombo', 'Matara', 'Hambantota', 'Ambalangoda']
  },
  Sweden: {
    'Stockholm County': ['Stockholm', 'Gothenburg', 'Malmö', 'Uppsala', 'Linköping'],
    'Västra Götaland': ['Gothenburg', 'Stockholm', 'Malmö', 'Borås', 'Trollhättan'],
    'Skåne County': ['Malmö', 'Stockholm', 'Gothenburg', 'Helsingborg', 'Lund']
  },
  Switzerland: {
    Zürich: ['Zürich', 'Geneva', 'Basel', 'Lausanne', 'Bern'],
    Bern: ['Bern', 'Zürich', 'Geneva', 'Basel', 'Lausanne'],
    'Vaud Canton': ['Lausanne', 'Geneva', 'Bern', 'Zurich', 'Basel']
  },
  Thailand: {
    Bangkok: ['Bangkok', 'Chiang Mai', 'Pattaya', 'Phuket', 'Khon Kaen'],
    'Chiang Mai Province': ['Chiang Mai', 'Chiang Rai', 'Mae Hong Son', 'Lampang', 'Nan'],
    'Chonburi Province': ['Pattaya', 'Bangkok', 'Chonburi', 'Sattahip', 'Laem Chabang']
  },
  Turkey: {
    Istanbul: ['Istanbul', 'Ankara', 'Izmir', 'Bursa', 'Antalya'],
    Ankara: ['Ankara', 'Istanbul', 'Izmir', 'Bursa', 'Antalya'],
    Izmir: ['Izmir', 'Istanbul', 'Ankara', 'Bursa', 'Antalya']
  },
  Uganda: {
    'Central Region': ['Kampala', 'Entebbe', 'Jinja', 'Mbale', 'Gulu'],
    'Western Region': ['Mbarara', 'Kasese', 'Fort Portal', 'Hoima', 'Masindi'],
    'Eastern Region': ['Jinja', 'Mbale', 'Tororo', 'Soroti', 'Lira']
  },
  Ukraine: {
    'Kyiv City': ['Kyiv', 'Kharkiv', 'Odesa', 'Dnipro', 'Donetsk'],
    'Kharkiv Oblast': ['Kharkiv', 'Kyiv', 'Dnipro', 'Odesa', 'Zaporizhzhia'],
    'Dnipropetrovsk Oblast': ['Dnipro', 'Kharkiv', 'Kyiv', 'Odesa', 'Poltava']
  },
  'United Arab Emirates': {
    'Abu Dhabi Emirate': ['Abu Dhabi', 'Dubai', 'Sharjah', 'Al Ain', 'Ajman'],
    'Dubai Emirate': ['Dubai', 'Abu Dhabi', 'Sharjah', 'Al Ain', 'Ajman'],
    'Sharjah Emirate': ['Sharjah', 'Dubai', 'Abu Dhabi', 'Al Ain', 'Ajman']
  },
  Vietnam: {
    'Ho Chi Minh City': ['Ho Chi Minh City', 'Hanoi', 'Da Nang', 'Hai Phong', 'Can Tho'],
    'Hanoi': ['Hanoi', 'Ho Chi Minh City', 'Hai Phong', 'Da Nang', 'Quang Ninh'],
    'Da Nang': ['Da Nang', 'Ho Chi Minh City', 'Hanoi', 'Hai Phong', 'Hue']
  },
  Zimbabwe: {
    Harare: ['Harare', 'Bulawayo', 'Mutare', 'Gweru', 'Epworth'],
    'Manicaland': ['Mutare', 'Harare', 'Chitungwiza', 'Gweru', 'Kadoma'],
    'Mashonaland East': ['Harare', 'Mutare', 'Chitungwiza', 'Marondera', 'Wedza']
  }
};