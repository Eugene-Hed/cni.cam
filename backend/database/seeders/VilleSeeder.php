<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VilleSeeder extends Seeder
{
    public function run(): void
    {
        $villes = [
            [1,1,'Tibati'],[2,1,'Ngaoundal'],[3,2,'Bankim'],[4,2,'Banyo'],
            [5,3,'Meiganga'],[6,3,'Dir'],[7,4,'Ngaoundéré'],[8,4,'Nganha'],
            [9,5,'Tignère'],[10,5,'Galim-Tignère'],[11,6,'Nanga Eboko'],[12,6,'Minta'],
            [13,7,'Monatélé'],[14,7,'Obala'],[15,7,'Ebebda'],[16,8,'Bafia'],
            [17,8,'Ombessa'],[18,8,'Ndikiniméki'],[19,9,'Ntui'],[20,9,'Yoko'],
            [21,10,'Mfou'],[22,10,'Afanloum'],[23,10,'Esse'],[24,11,'Akono'],
            [25,11,'Ngoumou'],[26,12,'Yaoundé'],[27,12,'Nkolafamba'],[28,13,'Eséka'],
            [29,13,'Makak'],[30,13,'Matomb'],[31,14,'Akonolinga'],[32,14,'Endom'],
            [33,15,'Mbalmayo'],[34,15,'Ngomedzap'],[35,15,'Meyomessala'],
            [36,16,'Yokadouma'],[37,16,'Moloundou'],[38,16,'Salapoumbé'],
            [39,17,'Abong-Mbang'],[40,17,'Doumé'],[41,17,'Messamena'],
            [42,18,'Batouri'],[43,18,'Kentzou'],[44,18,'Ndelele'],
            [45,19,'Bertoua'],[46,19,'Garoua-Boulaï'],[47,19,'Bélabo'],
            [48,20,'Maroua'],[49,20,'Bogo'],[50,20,'Dargala'],
            [51,21,'Kousséri'],[52,21,'Blangoua'],[53,21,'Makary'],
            [54,22,'Yagoua'],[55,22,'Gobo'],[56,22,'Gueme'],
            [57,23,'Kaélé'],[58,23,'Guidiguis'],[59,23,'Moutourwa'],
            [60,24,'Mora'],[61,24,'Tokombéré'],[62,24,'Kolofata'],
            [63,25,'Mokolo'],[64,25,'Bourrha'],[65,25,'Mayo-Moskota'],
            [66,26,'Nkongsamba'],[67,26,'Loum'],[68,26,'Melong'],
            [69,27,'Yabassi'],[70,27,'Nkondjock'],[71,27,'Dizangué'],
            [72,28,'Édéa'],[73,28,'Pouma'],[74,29,'Douala'],
            [75,29,'Manjo'],[76,29,'Dibombari'],[77,30,'Garoua'],
            [78,30,'Pitoa'],[79,30,'Demsa'],[80,31,'Poli'],
            [81,31,'Tcholliré'],[82,32,'Guider'],[83,32,'Figuil'],
            [84,33,'Tcholliré'],[85,33,'Touboro'],[86,34,'Fundong'],
            [87,34,'Belo'],[88,34,'Njinikom'],[89,35,'Kumbo'],
            [90,35,'Nkor'],[91,35,'Tatum'],[92,36,'Nkambe'],
            [93,36,'Misaje'],[94,36,'Ako'],[95,37,'Wum'],
            [96,37,'Furu-Awa'],[97,38,'Bamenda'],[98,38,'Bali'],
            [99,38,'Chomba'],[100,39,'Mbengwi'],[101,39,'Batibo'],
            [102,39,'Njikwa'],[103,40,'Ndop'],[104,40,'Babessi'],
            [105,40,'Bamessing'],[106,41,'Mbouda'],[107,41,'Galim'],
            [108,41,'Bamesso'],[109,42,'Bafang'],[110,42,'Kékem'],
            [111,42,'Bana'],[112,43,'Baham'],[113,43,'Batié'],
            [114,43,'Bandjoun'],[115,44,'Dschang'],[116,44,'Fongo-Tongo'],
            [117,44,'Fotetsa'],[118,45,'Bafoussam'],[119,45,'Bamendjou'],
            [120,45,'Bansoa'],[121,46,'Bangangté'],[122,46,'Tonga'],
            [123,46,'Bazou'],[124,47,'Foumban'],[125,47,'Koutaba'],
            [126,47,'Foumbot'],[127,48,'Sangmélima'],[128,48,'Meyomessala'],
            [129,48,'Zoétélé'],[130,49,'Ebolowa'],[131,49,'Mvangane'],
            [132,49,'Akom II'],[133,50,'Kribi'],[134,50,'Campo'],
            [135,50,'Bipindi'],[136,51,'Ambam'],[137,51,'Olamze'],
            [138,51,"Ma'an"],[139,52,'Limbe'],[140,52,'Buea'],
            [141,52,'Tiko'],[142,53,'Bangem'],[143,53,'Nguti'],
            [144,53,'Tombel'],[145,54,'Menji'],[146,54,'Alou'],
            [147,54,'Wabane'],[148,55,'Mamfe'],[149,55,'Eyumojock'],
            [150,55,'Akwaya'],[151,56,'Kumba'],[152,56,'Mbonge'],
            [153,56,'Konye'],[154,57,'Mundemba'],[155,57,'Isanguele'],
            [156,57,'Bamuso'],[157,40,'BAMEKA'],[158,40,'BAFOUSSAM'],
        ];

        $rows = [];
        foreach ($villes as $v) {
            $rows[] = ['VilleID' => $v[0], 'DepartementID' => $v[1], 'NomVille' => $v[2]];
        }

        // Insert in chunks to avoid packet size issues
        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('villes')->insert($chunk);
        }
    }
}
