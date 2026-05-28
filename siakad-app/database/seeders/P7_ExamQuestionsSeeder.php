<<<<<<< HEAD
<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class P7_ExamQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Membuat soal berbagai tipe untuk semua mapel...');

        $creator = User::where('role', 'guru')->first()
            ?? User::where('role', 'admin')->first()
            ?? User::first();

        if (!$creator) {
            $this->command->warn('Tidak ada user. Lewati P7_ExamQuestionsSeeder.');
            return;
        }

        $subjects = Subject::where('is_active', true)->get();
        $schoolId = $creator->school_id;

        $questionTypes = ['pg', 'bs', 'jodoh', 'esai'];
        $levels = ['L1', 'L2', 'L3'];
        $difficulties = ['mudah', 'sedang', 'sulit'];

        $bar = $this->command->getOutput()->createProgressBar($subjects->count());
        $bar->start();

        foreach ($subjects as $subject) {
            // Cari atau buat bank soal
            $bank = QuestionBank::firstOrCreate(
                ['subject_id' => $subject->id, 'school_id' => $schoolId],
                [
                    'name' => 'Bank Soal ' . $subject->name,
                    'class_id' => null,
                    'created_by' => $creator->id,
                    'is_shared' => true,
                ]
            );

            // Hapus soal lama di bank ini
            Question::where('question_bank_id', $bank->id)->delete();

            $questions = $this->getQuestionsForSubject($subject->code, $subject->name);

            foreach ($questions as $i => $qData) {
                Question::create([
                    'question_bank_id' => $bank->id,
                    'type' => $qData['type'],
                    'content' => $qData['content'],
                    'options' => $qData['options'] ?? null,
                    'answer_key' => $qData['answer_key'] ?? null,
                    'score' => $qData['score'] ?? 10,
                    'level_kognitif' => $levels[array_rand($levels)],
                    'difficulty' => $difficulties[array_rand($difficulties)],
                    'media' => null,
                    'created_by' => $creator->id,
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Soal berhasil dibuat untuk ' . $subjects->count() . ' mapel.');
    }

    private function getQuestionsForSubject(string $code, string $name): array
    {
        // Soal untuk tiap mapel: 2 PG, 1 BS, 1 Jodoh, 1 Audio, 1 Esai = 6 soal
        $map = [
            'PAI' => [
                ['type'=>'pg', 'content'=>'Rukun iman yang pertama adalah iman kepada...', 'options'=>json_encode((object)['A'=>'Allah SWT','B'=>'Malaikat','C'=>'Kitab-kitab','D'=>'Rasul','E'=>'Hari Akhir']), 'answer_key'=>'A'],
                ['type'=>'pg', 'content'=>'Shalat lima waktu hukumnya... bagi umat Islam yang sudah baligh.', 'options'=>json_encode((object)['A'=>'Sunnah','B'=>'Wajib','C'=>'Mubah','D'=>'Makruh','E'=>'Haram']), 'answer_key'=>'B'],
                ['type'=>'bs', 'content'=>'Zakat fitrah wajib dikeluarkan sebelum shalat Idul Fitri.', 'options'=>null, 'answer_key'=>'benar'],
                ['type'=>'jodoh', 'content'=>'Jodohkan nama malaikat dengan tugasnya!', 'options'=>json_encode((object)['Jibril'=>'Menyampaikan wahyu','Mikail'=>'Membagi rezeki','Israfil'=>'Meniup sangkakala','Izrail'=>'Mencabut nyawa','Raqib'=>'Mencatat amal baik']), 'answer_key'=>json_encode(['Jibril'=>'Menyampaikan wahyu','Mikail'=>'Membagi rezeki','Israfil'=>'Meniup sangkakala','Izrail'=>'Mencabut nyawa','Raqib'=>'Mencatat amal baik'])],
                ['type'=>'audio', 'content'=>'Dengarkan audio bacaan ayat berikut, lalu pilih arti yang tepat!', 'options'=>json_encode((object)['A'=>'Katakanlah: Dialah Allah Yang Maha Esa','B'=>'Segala puji bagi Allah Tuhan semesta alam','C'=>'Dengan nama Allah Yang Maha Pengasih Maha Penyayang','D'=>'Allah tempat meminta segala sesuatu','E'=>'Tunjukkanlah kami jalan yang lurus']), 'answer_key'=>'A'],
                ['type'=>'esai', 'content'=>'Jelaskan pengertian iman kepada kitab-kitab Allah SWT beserta contoh penerapannya dalam kehidupan sehari-hari!', 'options'=>null, 'answer_key'=>'Kunci: Iman kepada kitab berarti meyakini bahwa Allah menurunkan kitab kepada rasul-Nya sebagai pedoman. Contoh: membaca Al-Quran, mengamalkan isinya.'],
            ],
            'BIN' => [
                ['type'=>'pg', 'content'=>'Teks yang bertujuan menceritakan suatu peristiwa atau kejadian disebut teks...', 'options'=>json_encode((object)['A'=>'Anekdot','B'=>'Laporan Hasil Observasi','C'=>'Eksposisi','D'=>'Narasi','E'=>'Deskripsi']), 'answer_key'=>'D'],
                ['type'=>'pg', 'content'=>'Kalimat "Angin berbisik lembut di telinganya" menggunakan majas...', 'options'=>json_encode((object)['A'=>'Metafora','B'=>'Personifikasi','C'=>'Ironi','D'=>'Hiperbola','E'=>'Litotes']), 'answer_key'=>'B'],
                ['type'=>'bs', 'content'=>'Teks argumentasi bertujuan untuk mempengaruhi pembaca agar menerima pendapat penulis.', 'options'=>null, 'answer_key'=>'benar'],
                ['type'=>'jodoh', 'content'=>'Jodohkan jenis teks dengan tujuannya!', 'options'=>json_encode((object)['Narasi'=>'Menceritakan peristiwa','Deskripsi'=>'Menggambarkan objek','Eksposisi'=>'Menjelaskan informasi','Argumentasi'=>'Meyakinkan pembaca','Persuasi'=>'Mengajak pembaca']), 'answer_key'=>json_encode(['Narasi'=>'Menceritakan peristiwa','Deskripsi'=>'Menggambarkan objek','Eksposisi'=>'Menjelaskan informasi','Argumentasi'=>'Meyakinkan pembaca','Persuasi'=>'Mengajak pembaca'])],
                ['type'=>'audio', 'content'=>'Dengarkan penggalan teks berita yang diputar, lalu tentukan unsur 5W+1H yang paling menonjol!', 'options'=>json_encode((object)['A'=>'What (Apa)','B'=>'Who (Siapa)','C'=>'When (Kapan)','D'=>'Where (Di mana)','E'=>'Why (Mengapa)']), 'answer_key'=>'B'],
                ['type'=>'esai', 'content'=>'Buatlah sebuah paragraf deskripsi tentang suasana sekolahmu pada pagi hari (minimal 5 kalimat)!', 'options'=>null, 'answer_key'=>'Kunci: Paragraf deskripsi harus menggambarkan suasana menggunakan panca indera, minimal 5 kalimat.'],
            ],
            'MTK' => [
                ['type'=>'pg', 'content'=>'Hasil dari 2x² + 3x - 5 untuk x = 2 adalah...', 'options'=>json_encode((object)['A'=>'5','B'=>'9','C'=>'11','D'=>'15','E'=>'21']), 'answer_key'=>'B'],
                ['type'=>'pg', 'content'=>'Diketahui segitiga siku-siku dengan sisi miring 13 cm dan salah satu sisi 5 cm. Panjang sisi lainnya adalah...', 'options'=>json_encode((object)['A'=>'8 cm','B'=>'10 cm','C'=>'12 cm','D'=>'14 cm','E'=>'18 cm']), 'answer_key'=>'C'],
                ['type'=>'bs', 'content'=>'Akar-akar persamaan kuadrat x² - 5x + 6 = 0 adalah 2 dan 3.', 'options'=>null, 'answer_key'=>'benar'],
                ['type'=>'jodoh', 'content'=>'Jodohkan fungsi dengan turunannya!', 'options'=>json_encode((object)['f(x)=x³'=>'3x²','f(x)=sin x'=>'cos x','f(x)=eˣ'=>'eˣ','f(x)=ln x'=>'1/x','f(x)=2x²+3x'=>'4x+3']), 'answer_key'=>json_encode(['f(x)=x³'=>'3x²','f(x)=sin x'=>'cos x','f(x)=eˣ'=>'eˣ','f(x)=ln x'=>'1/x','f(x)=2x²+3x'=>'4x+3'])],
                ['type'=>'audio', 'content'=>'Dengarkan penjelasan tentang cara menghitung volume, lalu pilih rumus yang benar!', 'options'=>json_encode((object)['A'=>'V = p × l × t','B'=>'V = s × s × s','C'=>'V = π × r² × t','D'=>'V = 1/3 × π × r² × t','E'=>'V = 4/3 × π × r³']), 'answer_key'=>'A'],
                ['type'=>'esai', 'content'=>'Sebuah tangga sepanjang 10 meter disandarkan pada dinding. Jarak kaki tangga ke dinding 6 meter. Hitunglah tinggi dinding yang dicapai tangga menggunakan teorema Pythagoras!', 'options'=>null, 'answer_key'=>'tinggi = √(10² - 6²) = √(100-36) = √64 = 8 meter'],
            ],
            'ING' => [
                ['type'=>'pg', 'content'=>'She ___ to school every morning.', 'options'=>json_encode((object)['A'=>'go','B'=>'goes','C'=>'going','D'=>'gone','E'=>'went']), 'answer_key'=>'B'],
                ['type'=>'pg', 'content'=>'The past tense of "write" is...', 'options'=>json_encode((object)['A'=>'writed','B'=>'written','C'=>'wrote','D'=>'writing','E'=>'writes']), 'answer_key'=>'C'],
                ['type'=>'bs', 'content'=>'"I have been studying English for 3 years" is an example of Present Perfect Continuous tense.', 'options'=>null, 'answer_key'=>'benar'],
                ['type'=>'jodoh', 'content'=>'Jodohkan kata dengan artinya!', 'options'=>json_encode((object)['Beautiful'=>'Cantik','Brave'=>'Berani','Clever'=>'Pintar','Diligent'=>'Rajin','Honest'=>'Jujur']), 'answer_key'=>json_encode(['Beautiful'=>'Cantik','Brave'=>'Berani','Clever'=>'Pintar','Diligent'=>'Rajin','Honest'=>'Jujur'])],
                ['type'=>'audio', 'content'=>'Listen to the audio and choose the correct response!', 'options'=>json_encode((object)['A'=>'How do you do?','B'=>'I am fine, thank you.','C'=>'Good morning!','D'=>'See you later!','E'=>'Nice to meet you.']), 'answer_key'=>'B'],
                ['type'=>'esai', 'content'=>'Write a short paragraph (5 sentences) about your daily activities using Simple Present Tense!', 'options'=>null, 'answer_key'=>'Kunci: Menggunakan Simple Present Tense (V1/V1+s/es), minimal 5 kalimat.'],
            ],
            'FIS' => [
                ['type'=>'pg', 'content'=>'Sebuah benda bermassa 2 kg mengalami percepatan 3 m/s². Gaya yang bekerja adalah...', 'options'=>json_encode((object)['A'=>'2 N','B'=>'3 N','C'=>'5 N','D'=>'6 N','E'=>'9 N']), 'answer_key'=>'D'],
                ['type'=>'pg', 'content'=>'Energi kinetik benda bermassa 4 kg bergerak dengan kecepatan 10 m/s adalah...', 'options'=>json_encode((object)['A'=>'40 J','B'=>'80 J','C'=>'100 J','D'=>'200 J','E'=>'400 J']), 'answer_key'=>'D'],
                ['type'=>'bs', 'content'=>'Hukum I Newton menyatakan bahwa benda akan tetap diam atau bergerak lurus beraturan jika resultan gaya = 0.', 'options'=>null, 'answer_key'=>'benar'],
                ['type'=>'jodoh', 'content'=>'Jodohkan besaran dengan satuannya!', 'options'=>json_encode((object)['Gaya'=>'Newton','Energi'=>'Joule','Daya'=>'Watt','Tekanan'=>'Pascal','Frekuensi'=>'Hertz']), 'answer_key'=>json_encode(['Gaya'=>'Newton','Energi'=>'Joule','Daya'=>'Watt','Tekanan'=>'Pascal','Frekuensi'=>'Hertz'])],
                ['type'=>'audio', 'content'=>'Dengarkan penjelasan tentang Hukum Ohm, lalu pilih pernyataan yang benar!', 'options'=>json_encode((object)['A'=>'V = I × R','B'=>'P = V × I','C'=>'W = F × s','D'=>'F = m × a','E'=>'E = m × c²']), 'answer_key'=>'A'],
                ['type'=>'esai', 'content'=>'Jelaskan perbedaan antara energi potensial dan energi kinetik, serta berikan masing-masing satu contoh!', 'options'=>null, 'answer_key'=>'Energi potensial: energi karena posisi (contoh: benda di ketinggian). Energi kinetik: energi karena gerak (contoh: mobil berjalan).'],
            ],
            'SJH' => [
                ['type'=>'pg', 'content'=>'Proklamasi kemerdekaan Indonesia dibacakan pada tanggal...', 'options'=>json_encode((object)['A'=>'17 Agustus 1944','B'=>'17 Agustus 1945','C'=>'18 Agustus 1945','D'=>'17 Agustus 1946','E'=>'18 Agustus 1946']), 'answer_key'=>'B'],
                ['type'=>'pg', 'content'=>'BPUPKI dibentuk pada masa pendudukan...', 'options'=>json_encode((object)['A'=>'Belanda','B'=>'Inggris','C'=>'Jepang','D'=>'Portugis','E'=>'Spanyol']), 'answer_key'=>'C'],
                ['type'=>'bs', 'content'=>'Sumpah Pemuda diikrarkan pada tanggal 28 Oktober 1928.', 'options'=>null, 'answer_key'=>'benar'],
                ['type'=>'jodoh', 'content'=>'Jodohkan tokoh dengan perannya!', 'options'=>json_encode((object)['Soekarno'=>'Proklamator','Moh Hatta'=>'Wakil Proklamator','Sutan Sjahrir'=>'Perdana Menteri I','Jenderal Sudirman'=>'Panglima TNI I','Moh Yamin'=>'Perumus Pancasila']), 'answer_key'=>json_encode(['Soekarno'=>'Proklamator','Moh Hatta'=>'Wakil Proklamator','Sutan Sjahrir'=>'Perdana Menteri I','Jenderal Sudirman'=>'Panglima TNI I','Moh Yamin'=>'Perumus Pancasila'])],
                ['type'=>'audio', 'content'=>'Dengarkan rekaman pembacaan teks proklamasi, lalu pilih tokoh yang membacakannya!', 'options'=>json_encode((object)['A'=>'Ir. Soekarno','B'=>'Drs. Moh. Hatta','C'=>'Sutan Sjahrir','D'=>'Jenderal Sudirman','E'=>'Ahmad Subardjo']), 'answer_key'=>'A'],
                ['type'=>'esai', 'content'=>'Jelaskan latar belakang dan dampak dari peristiwa Rengasdengklok bagi kemerdekaan Indonesia!', 'options'=>null, 'answer_key'=>'Latar: perbedaan pendapat golongan tua dan muda tentang waktu proklamasi. Dampak: mempercepat proklamasi 17 Agustus 1945.'],
            ],
            // Generic fallback untuk mapel lainnya
            'default' => [
                ['type'=>'pg', 'content'=>'Pertanyaan Pilihan Ganda untuk ' . $name, 'options'=>json_encode((object)['A'=>'Jawaban A','B'=>'Jawaban B','C'=>'Jawaban C','D'=>'Jawaban D','E'=>'Jawaban E']), 'answer_key'=>'C'],
                ['type'=>'pg', 'content'=>'Soal kedua Pilihan Ganda untuk ' . $name, 'options'=>json_encode((object)['A'=>'Opsi 1','B'=>'Opsi 2','C'=>'Opsi 3','D'=>'Opsi 4','E'=>'Opsi 5']), 'answer_key'=>'A'],
                ['type'=>'bs', 'content'=>'Pernyataan Benar/Salah terkait materi ' . $name . '.', 'options'=>null, 'answer_key'=>'benar'],
                ['type'=>'jodoh', 'content'=>'Jodohkan istilah dengan definisinya pada mapel ' . $name . '!', 'options'=>json_encode((object)['Istilah A'=>'Definisi 1','Istilah B'=>'Definisi 2','Istilah C'=>'Definisi 3','Istilah D'=>'Definisi 4','Istilah E'=>'Definisi 5']), 'answer_key'=>json_encode(['Istilah A'=>'Definisi 1','Istilah B'=>'Definisi 2','Istilah C'=>'Definisi 3','Istilah D'=>'Definisi 4','Istilah E'=>'Definisi 5'])],
                ['type'=>'audio', 'content'=>'Dengarkan audio pembelajaran berikut, lalu pilih jawaban yang paling tepat!', 'options'=>json_encode((object)['A'=>'Pilihan A','B'=>'Pilihan B','C'=>'Pilihan C','D'=>'Pilihan D','E'=>'Pilihan E']), 'answer_key'=>'C'],
                ['type'=>'esai', 'content'=>'Jelaskan secara singkat salah satu konsep penting dalam mata pelajaran ' . $name . '!', 'options'=>null, 'answer_key'=>'Kunci: Jawaban harus mencakup definisi, contoh, dan penerapan.'],
            ],
        ];

        return $map[$code] ?? $map['default'];
    }
}
=======
<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class P7_ExamQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Membuat soal berbagai tipe untuk semua mapel...');

        $creator = User::where('role', 'guru')->first()
            ?? User::where('role', 'admin')->first()
            ?? User::first();

        if (!$creator) {
            $this->command->warn('Tidak ada user. Lewati P7_ExamQuestionsSeeder.');
            return;
        }

        $subjects = Subject::where('is_active', true)->get();
        $schoolId = $creator->school_id;

        $questionTypes = ['pg', 'bs', 'jodoh', 'esai'];
        $levels = ['L1', 'L2', 'L3'];
        $difficulties = ['mudah', 'sedang', 'sulit'];

        $bar = $this->command->getOutput()->createProgressBar($subjects->count());
        $bar->start();

        foreach ($subjects as $subject) {
            // Cari atau buat bank soal
            $bank = QuestionBank::firstOrCreate(
                ['subject_id' => $subject->id, 'school_id' => $schoolId],
                [
                    'name' => 'Bank Soal ' . $subject->name,
                    'class_id' => null,
                    'created_by' => $creator->id,
                    'is_shared' => true,
                ]
            );

            // Hapus soal lama di bank ini
            Question::where('question_bank_id', $bank->id)->delete();

            $questions = $this->getQuestionsForSubject($subject->code, $subject->name);

            foreach ($questions as $i => $qData) {
                Question::create([
                    'question_bank_id' => $bank->id,
                    'type' => $qData['type'],
                    'content' => $qData['content'],
                    'options' => $qData['options'] ?? null,
                    'answer_key' => $qData['answer_key'] ?? null,
                    'score' => $qData['score'] ?? 10,
                    'level_kognitif' => $levels[array_rand($levels)],
                    'difficulty' => $difficulties[array_rand($difficulties)],
                    'media' => null,
                    'created_by' => $creator->id,
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Soal berhasil dibuat untuk ' . $subjects->count() . ' mapel.');
    }

    private function getQuestionsForSubject(string $code, string $name): array
    {
        // Soal untuk tiap mapel: 2 PG, 1 BS, 1 Jodoh, 1 Audio, 1 Esai = 6 soal
        $map = [
            'PAI' => [
                ['type'=>'pg', 'content'=>'Rukun iman yang pertama adalah iman kepada...', 'options'=>json_encode((object)['A'=>'Allah SWT','B'=>'Malaikat','C'=>'Kitab-kitab','D'=>'Rasul','E'=>'Hari Akhir']), 'answer_key'=>'A'],
                ['type'=>'pg', 'content'=>'Shalat lima waktu hukumnya... bagi umat Islam yang sudah baligh.', 'options'=>json_encode((object)['A'=>'Sunnah','B'=>'Wajib','C'=>'Mubah','D'=>'Makruh','E'=>'Haram']), 'answer_key'=>'B'],
                ['type'=>'bs', 'content'=>'Zakat fitrah wajib dikeluarkan sebelum shalat Idul Fitri.', 'options'=>null, 'answer_key'=>'benar'],
                ['type'=>'jodoh', 'content'=>'Jodohkan nama malaikat dengan tugasnya!', 'options'=>json_encode((object)['Jibril'=>'Menyampaikan wahyu','Mikail'=>'Membagi rezeki','Israfil'=>'Meniup sangkakala','Izrail'=>'Mencabut nyawa','Raqib'=>'Mencatat amal baik']), 'answer_key'=>json_encode(['Jibril'=>'Menyampaikan wahyu','Mikail'=>'Membagi rezeki','Israfil'=>'Meniup sangkakala','Izrail'=>'Mencabut nyawa','Raqib'=>'Mencatat amal baik'])],
                ['type'=>'audio', 'content'=>'Dengarkan audio bacaan ayat berikut, lalu pilih arti yang tepat!', 'options'=>json_encode((object)['A'=>'Katakanlah: Dialah Allah Yang Maha Esa','B'=>'Segala puji bagi Allah Tuhan semesta alam','C'=>'Dengan nama Allah Yang Maha Pengasih Maha Penyayang','D'=>'Allah tempat meminta segala sesuatu','E'=>'Tunjukkanlah kami jalan yang lurus']), 'answer_key'=>'A'],
                ['type'=>'esai', 'content'=>'Jelaskan pengertian iman kepada kitab-kitab Allah SWT beserta contoh penerapannya dalam kehidupan sehari-hari!', 'options'=>null, 'answer_key'=>'Kunci: Iman kepada kitab berarti meyakini bahwa Allah menurunkan kitab kepada rasul-Nya sebagai pedoman. Contoh: membaca Al-Quran, mengamalkan isinya.'],
            ],
            'BIN' => [
                ['type'=>'pg', 'content'=>'Teks yang bertujuan menceritakan suatu peristiwa atau kejadian disebut teks...', 'options'=>json_encode((object)['A'=>'Anekdot','B'=>'Laporan Hasil Observasi','C'=>'Eksposisi','D'=>'Narasi','E'=>'Deskripsi']), 'answer_key'=>'D'],
                ['type'=>'pg', 'content'=>'Kalimat "Angin berbisik lembut di telinganya" menggunakan majas...', 'options'=>json_encode((object)['A'=>'Metafora','B'=>'Personifikasi','C'=>'Ironi','D'=>'Hiperbola','E'=>'Litotes']), 'answer_key'=>'B'],
                ['type'=>'bs', 'content'=>'Teks argumentasi bertujuan untuk mempengaruhi pembaca agar menerima pendapat penulis.', 'options'=>null, 'answer_key'=>'benar'],
                ['type'=>'jodoh', 'content'=>'Jodohkan jenis teks dengan tujuannya!', 'options'=>json_encode((object)['Narasi'=>'Menceritakan peristiwa','Deskripsi'=>'Menggambarkan objek','Eksposisi'=>'Menjelaskan informasi','Argumentasi'=>'Meyakinkan pembaca','Persuasi'=>'Mengajak pembaca']), 'answer_key'=>json_encode(['Narasi'=>'Menceritakan peristiwa','Deskripsi'=>'Menggambarkan objek','Eksposisi'=>'Menjelaskan informasi','Argumentasi'=>'Meyakinkan pembaca','Persuasi'=>'Mengajak pembaca'])],
                ['type'=>'audio', 'content'=>'Dengarkan penggalan teks berita yang diputar, lalu tentukan unsur 5W+1H yang paling menonjol!', 'options'=>json_encode((object)['A'=>'What (Apa)','B'=>'Who (Siapa)','C'=>'When (Kapan)','D'=>'Where (Di mana)','E'=>'Why (Mengapa)']), 'answer_key'=>'B'],
                ['type'=>'esai', 'content'=>'Buatlah sebuah paragraf deskripsi tentang suasana sekolahmu pada pagi hari (minimal 5 kalimat)!', 'options'=>null, 'answer_key'=>'Kunci: Paragraf deskripsi harus menggambarkan suasana menggunakan panca indera, minimal 5 kalimat.'],
            ],
            'MTK' => [
                ['type'=>'pg', 'content'=>'Hasil dari 2x² + 3x - 5 untuk x = 2 adalah...', 'options'=>json_encode((object)['A'=>'5','B'=>'9','C'=>'11','D'=>'15','E'=>'21']), 'answer_key'=>'B'],
                ['type'=>'pg', 'content'=>'Diketahui segitiga siku-siku dengan sisi miring 13 cm dan salah satu sisi 5 cm. Panjang sisi lainnya adalah...', 'options'=>json_encode((object)['A'=>'8 cm','B'=>'10 cm','C'=>'12 cm','D'=>'14 cm','E'=>'18 cm']), 'answer_key'=>'C'],
                ['type'=>'bs', 'content'=>'Akar-akar persamaan kuadrat x² - 5x + 6 = 0 adalah 2 dan 3.', 'options'=>null, 'answer_key'=>'benar'],
                ['type'=>'jodoh', 'content'=>'Jodohkan fungsi dengan turunannya!', 'options'=>json_encode((object)['f(x)=x³'=>'3x²','f(x)=sin x'=>'cos x','f(x)=eˣ'=>'eˣ','f(x)=ln x'=>'1/x','f(x)=2x²+3x'=>'4x+3']), 'answer_key'=>json_encode(['f(x)=x³'=>'3x²','f(x)=sin x'=>'cos x','f(x)=eˣ'=>'eˣ','f(x)=ln x'=>'1/x','f(x)=2x²+3x'=>'4x+3'])],
                ['type'=>'audio', 'content'=>'Dengarkan penjelasan tentang cara menghitung volume, lalu pilih rumus yang benar!', 'options'=>json_encode((object)['A'=>'V = p × l × t','B'=>'V = s × s × s','C'=>'V = π × r² × t','D'=>'V = 1/3 × π × r² × t','E'=>'V = 4/3 × π × r³']), 'answer_key'=>'A'],
                ['type'=>'esai', 'content'=>'Sebuah tangga sepanjang 10 meter disandarkan pada dinding. Jarak kaki tangga ke dinding 6 meter. Hitunglah tinggi dinding yang dicapai tangga menggunakan teorema Pythagoras!', 'options'=>null, 'answer_key'=>'tinggi = √(10² - 6²) = √(100-36) = √64 = 8 meter'],
            ],
            'ING' => [
                ['type'=>'pg', 'content'=>'She ___ to school every morning.', 'options'=>json_encode((object)['A'=>'go','B'=>'goes','C'=>'going','D'=>'gone','E'=>'went']), 'answer_key'=>'B'],
                ['type'=>'pg', 'content'=>'The past tense of "write" is...', 'options'=>json_encode((object)['A'=>'writed','B'=>'written','C'=>'wrote','D'=>'writing','E'=>'writes']), 'answer_key'=>'C'],
                ['type'=>'bs', 'content'=>'"I have been studying English for 3 years" is an example of Present Perfect Continuous tense.', 'options'=>null, 'answer_key'=>'benar'],
                ['type'=>'jodoh', 'content'=>'Jodohkan kata dengan artinya!', 'options'=>json_encode((object)['Beautiful'=>'Cantik','Brave'=>'Berani','Clever'=>'Pintar','Diligent'=>'Rajin','Honest'=>'Jujur']), 'answer_key'=>json_encode(['Beautiful'=>'Cantik','Brave'=>'Berani','Clever'=>'Pintar','Diligent'=>'Rajin','Honest'=>'Jujur'])],
                ['type'=>'audio', 'content'=>'Listen to the audio and choose the correct response!', 'options'=>json_encode((object)['A'=>'How do you do?','B'=>'I am fine, thank you.','C'=>'Good morning!','D'=>'See you later!','E'=>'Nice to meet you.']), 'answer_key'=>'B'],
                ['type'=>'esai', 'content'=>'Write a short paragraph (5 sentences) about your daily activities using Simple Present Tense!', 'options'=>null, 'answer_key'=>'Kunci: Menggunakan Simple Present Tense (V1/V1+s/es), minimal 5 kalimat.'],
            ],
            'FIS' => [
                ['type'=>'pg', 'content'=>'Sebuah benda bermassa 2 kg mengalami percepatan 3 m/s². Gaya yang bekerja adalah...', 'options'=>json_encode((object)['A'=>'2 N','B'=>'3 N','C'=>'5 N','D'=>'6 N','E'=>'9 N']), 'answer_key'=>'D'],
                ['type'=>'pg', 'content'=>'Energi kinetik benda bermassa 4 kg bergerak dengan kecepatan 10 m/s adalah...', 'options'=>json_encode((object)['A'=>'40 J','B'=>'80 J','C'=>'100 J','D'=>'200 J','E'=>'400 J']), 'answer_key'=>'D'],
                ['type'=>'bs', 'content'=>'Hukum I Newton menyatakan bahwa benda akan tetap diam atau bergerak lurus beraturan jika resultan gaya = 0.', 'options'=>null, 'answer_key'=>'benar'],
                ['type'=>'jodoh', 'content'=>'Jodohkan besaran dengan satuannya!', 'options'=>json_encode((object)['Gaya'=>'Newton','Energi'=>'Joule','Daya'=>'Watt','Tekanan'=>'Pascal','Frekuensi'=>'Hertz']), 'answer_key'=>json_encode(['Gaya'=>'Newton','Energi'=>'Joule','Daya'=>'Watt','Tekanan'=>'Pascal','Frekuensi'=>'Hertz'])],
                ['type'=>'audio', 'content'=>'Dengarkan penjelasan tentang Hukum Ohm, lalu pilih pernyataan yang benar!', 'options'=>json_encode((object)['A'=>'V = I × R','B'=>'P = V × I','C'=>'W = F × s','D'=>'F = m × a','E'=>'E = m × c²']), 'answer_key'=>'A'],
                ['type'=>'esai', 'content'=>'Jelaskan perbedaan antara energi potensial dan energi kinetik, serta berikan masing-masing satu contoh!', 'options'=>null, 'answer_key'=>'Energi potensial: energi karena posisi (contoh: benda di ketinggian). Energi kinetik: energi karena gerak (contoh: mobil berjalan).'],
            ],
            'SJH' => [
                ['type'=>'pg', 'content'=>'Proklamasi kemerdekaan Indonesia dibacakan pada tanggal...', 'options'=>json_encode((object)['A'=>'17 Agustus 1944','B'=>'17 Agustus 1945','C'=>'18 Agustus 1945','D'=>'17 Agustus 1946','E'=>'18 Agustus 1946']), 'answer_key'=>'B'],
                ['type'=>'pg', 'content'=>'BPUPKI dibentuk pada masa pendudukan...', 'options'=>json_encode((object)['A'=>'Belanda','B'=>'Inggris','C'=>'Jepang','D'=>'Portugis','E'=>'Spanyol']), 'answer_key'=>'C'],
                ['type'=>'bs', 'content'=>'Sumpah Pemuda diikrarkan pada tanggal 28 Oktober 1928.', 'options'=>null, 'answer_key'=>'benar'],
                ['type'=>'jodoh', 'content'=>'Jodohkan tokoh dengan perannya!', 'options'=>json_encode((object)['Soekarno'=>'Proklamator','Moh Hatta'=>'Wakil Proklamator','Sutan Sjahrir'=>'Perdana Menteri I','Jenderal Sudirman'=>'Panglima TNI I','Moh Yamin'=>'Perumus Pancasila']), 'answer_key'=>json_encode(['Soekarno'=>'Proklamator','Moh Hatta'=>'Wakil Proklamator','Sutan Sjahrir'=>'Perdana Menteri I','Jenderal Sudirman'=>'Panglima TNI I','Moh Yamin'=>'Perumus Pancasila'])],
                ['type'=>'audio', 'content'=>'Dengarkan rekaman pembacaan teks proklamasi, lalu pilih tokoh yang membacakannya!', 'options'=>json_encode((object)['A'=>'Ir. Soekarno','B'=>'Drs. Moh. Hatta','C'=>'Sutan Sjahrir','D'=>'Jenderal Sudirman','E'=>'Ahmad Subardjo']), 'answer_key'=>'A'],
                ['type'=>'esai', 'content'=>'Jelaskan latar belakang dan dampak dari peristiwa Rengasdengklok bagi kemerdekaan Indonesia!', 'options'=>null, 'answer_key'=>'Latar: perbedaan pendapat golongan tua dan muda tentang waktu proklamasi. Dampak: mempercepat proklamasi 17 Agustus 1945.'],
            ],
            // Generic fallback untuk mapel lainnya
            'default' => [
                ['type'=>'pg', 'content'=>'Pertanyaan Pilihan Ganda untuk ' . $name, 'options'=>json_encode((object)['A'=>'Jawaban A','B'=>'Jawaban B','C'=>'Jawaban C','D'=>'Jawaban D','E'=>'Jawaban E']), 'answer_key'=>'C'],
                ['type'=>'pg', 'content'=>'Soal kedua Pilihan Ganda untuk ' . $name, 'options'=>json_encode((object)['A'=>'Opsi 1','B'=>'Opsi 2','C'=>'Opsi 3','D'=>'Opsi 4','E'=>'Opsi 5']), 'answer_key'=>'A'],
                ['type'=>'bs', 'content'=>'Pernyataan Benar/Salah terkait materi ' . $name . '.', 'options'=>null, 'answer_key'=>'benar'],
                ['type'=>'jodoh', 'content'=>'Jodohkan istilah dengan definisinya pada mapel ' . $name . '!', 'options'=>json_encode((object)['Istilah A'=>'Definisi 1','Istilah B'=>'Definisi 2','Istilah C'=>'Definisi 3','Istilah D'=>'Definisi 4','Istilah E'=>'Definisi 5']), 'answer_key'=>json_encode(['Istilah A'=>'Definisi 1','Istilah B'=>'Definisi 2','Istilah C'=>'Definisi 3','Istilah D'=>'Definisi 4','Istilah E'=>'Definisi 5'])],
                ['type'=>'audio', 'content'=>'Dengarkan audio pembelajaran berikut, lalu pilih jawaban yang paling tepat!', 'options'=>json_encode((object)['A'=>'Pilihan A','B'=>'Pilihan B','C'=>'Pilihan C','D'=>'Pilihan D','E'=>'Pilihan E']), 'answer_key'=>'C'],
                ['type'=>'esai', 'content'=>'Jelaskan secara singkat salah satu konsep penting dalam mata pelajaran ' . $name . '!', 'options'=>null, 'answer_key'=>'Kunci: Jawaban harus mencakup definisi, contoh, dan penerapan.'],
            ],
        ];

        return $map[$code] ?? $map['default'];
    }
}
>>>>>>> 6a558e6af3d2739ca3b59ac14b9fe5dbe6c2e3f6
