<?php

namespace App\Support;

/**
 * Turns curated complete sentences into varied, reproducible typing prompts.
 *
 * A random seed is signed by TypingController and returned to the browser.
 * Rebuilding the exact same prompt during score submission avoids trusting
 * client-supplied reference text and needs no new database tables.
 */
final class TypingPromptBuilder
{
    private const SENTENCES = [
        'en' => [
            'easy' => [
                'We enjoy reading after school.',
                'I can type simple words.',
                'We learn new words while playing games with our friends.',
                'The sun shines over our town.',
                'My sister likes to read books.',
                'We can walk home after lunch.',
                'A small bird sits nearby.',
                'The dog sleeps near the open door.',
                'I drink water before starting my day.',
                'Our teacher writes a word on the board.',
                'The garden looks fresh after the rain.',
                'Please close the window when you leave.',
                'Every morning brings a chance to learn.',
                'My friend has a bright blue bag.',
                'The children play outside after school.',
                'A warm meal makes everyone feel better.',
                'We share stories around the dinner table.',
                'You can find pencils in that drawer.',
                'The little cat enjoys a quiet place.',
                'Today we learn something new together.',
                'Her favorite song plays on the radio.',
                'The road leads to a peaceful village.',
                'A clean desk helps me stay focused.',
            ],
            'normal' => [
                'Careful practice builds lasting confidence.',
                'Focus brings clarity during practice.',
                'We improve our typing speed through regular and focused practice.',
                'Small improvements become meaningful when we repeat them every day.',
                'Small steps lead to lasting progress.',
                'Practice helps you build better habits.',
                'A steady rhythm makes typing feel more natural.',
                'Each new attempt gives you a chance to improve.',
                'People learn faster when they understand their mistakes.',
                'A short break can help you regain your focus.',
                'Consistency matters more than a single impressive result.',
                'The best ideas often begin with simple questions.',
                'Typing accurately is just as important as typing quickly.',
                'Our daily routines shape the skills we develop.',
                'A good plan leaves room for unexpected changes.',
                'Clear communication helps every team work together.',
                'Reading widely introduces us to different perspectives.',
                'It takes patience to master a challenging skill.',
                'A quiet environment can make difficult tasks easier.',
                'Learning something useful rarely happens all at once.',
                'A thoughtful question can start an interesting conversation.',
                'Strong foundations make future challenges less intimidating.',
                'Progress becomes visible when you track small improvements.',
                'Creativity grows when curiosity meets regular practice.',
                'Finding your own pace is part of the process.',
                'You can improve your accuracy without rushing every sentence.',
                'Even familiar tasks become easier with deliberate attention.',
                'Good habits often begin with one careful decision.',
            ],
            'hard' => [
                'Check all details before submitting.',
                'Save changes before closing files.',
                'The update starts at 08:45; please check your email first.',
                'The meeting starts at 08:45; please arrive 10 minutes early.',
                'Before submitting, review sections A, B, and C carefully.',
                'Your order #4821 was updated on 12/08/2026.',
                'Remember: accuracy comes first, speed comes second!',
                'The report showed a 17.5% increase over last month.',
                'He asked, "Did you save the final version?"',
                'Check the file named project_v2-final.txt before leaving.',
                'At 9:30 p.m., the system sent a status update.',
                'A price of $24.99 is different from $29.49.',
                'Please use the format name@example.com for this form.',
                'The password hint included 3 symbols: @, #, and !.',
                'A 2-step verification process protects your account.',
                'According to the chart, 64 out of 100 users agreed.',
                'Try pressing Ctrl+S before closing the document.',
                'A short message can still contain useful details.',
                'Version 3.2.1 fixes several issues from release 3.1.',
                'The package weighs 1.25 kg, including its label.',
                'The address ends with unit #07, not unit #70.',
                'Wait 5 seconds, then press the button marked "Next".',
                'Her notes listed three priorities: quality, speed, and care.',
            ],
            'expert' => [
                'Always validate incoming API requests.',
                'Review exceptions before deploying updates.',
                'A Laravel route can validate input before saving database records.',
                'In JavaScript, const ready = true; declares a fixed binding.',
                'The function validate(input) should return a boolean value.',
                'A query such as SELECT id FROM users; returns matching data.',
                'Use git status before creating a new commit.',
                'The API returned {"ok":true,"count":12} after the request.',
                'An HTTP 404 response indicates that a resource was not found.',
                'Check whether user?.profile?.name is defined before rendering.',
                'The command npm run build creates optimized frontend assets.',
                'A route like /api/users/42 can reference a single record.',
                'Avoid storing passwords directly in plain text files.',
                'The array items.map(item => item.id) extracts identifiers.',
                'Use try { await load(); } catch (error) { log(error); }.',
                'A JSON payload must use double quotes around its keys.',
                'The condition count >= 10 checks a numeric threshold.',
                'An indexed database column may improve lookup performance.',
                'A 500 error usually means the server failed unexpectedly.',
                'Review the result of php artisan migrate:status first.',
                'The expression total = price * quantity computes a subtotal.',
                'Use strict comparison, such as value === null, when possible.',
                'The endpoint requires a valid token in its request header.',
            ],
        ],
        'id' => [
            'easy' => [
                'Kami bermain bersama setelah sekolah.',
                'Aku membaca buku setiap malam.',
                'Kami pulang ke rumah setelah menyelesaikan tugas bersama di sekolah.',
                'Aku suka membaca buku setiap pagi.',
                'Burung kecil terbang di atas rumah.',
                'Kami berjalan kaki menuju sekolah bersama.',
                'Ibu menyiapkan sarapan untuk seluruh keluarga.',
                'Langit terlihat cerah setelah hujan reda.',
                'Adik bermain bola di halaman rumah.',
                'Temanku membawa tas berwarna biru.',
                'Kami belajar bersama di ruang kelas.',
                'Kucing itu tidur dekat pintu rumah.',
                'Aku minum air setelah selesai bermain.',
                'Bunga merah tumbuh di depan pagar.',
                'Ayah membaca koran sambil duduk santai.',
                'Hari ini kami membuat karya baru.',
                'Kakak menulis cerita di buku catatan.',
                'Semua siswa masuk kelas tepat waktu.',
                'Aku menyimpan pensil di dalam kotak.',
                'Jalan desa tampak tenang pada sore hari.',
                'Kami makan malam sambil berbagi cerita.',
                'Udara pagi terasa segar dan sejuk.',
                'Anak itu tersenyum kepada teman barunya.',
            ],
            'normal' => [
                'Latihan teratur membuat kemajuan nyata.',
                'Ketelitian membantu mengurangi kesalahan kecil.',
                'Kita belajar lebih cepat saat memahami kesalahan dengan sikap terbuka.',
                'Latihan rutin membantu meningkatkan kemampuan mengetik setiap hari.',
                'Kebiasaan kecil dapat membawa perubahan yang berarti.',
                'Setiap kesalahan memberi kesempatan untuk belajar kembali.',
                'Kecepatan akan meningkat jika ketelitian tetap dijaga.',
                'Belajar hal baru memerlukan waktu dan kesabaran.',
                'Suasana yang tenang membantu kita menjaga konsentrasi.',
                'Sebuah rencana yang baik tetap membutuhkan penyesuaian.',
                'Kerja sama yang sehat dimulai dari komunikasi terbuka.',
                'Membaca banyak buku dapat memperluas sudut pandang.',
                'Kemajuan kecil layak dihargai selama kita terus berusaha.',
                'Teknologi dapat membantu pekerjaan jika digunakan dengan bijak.',
                'Setiap proyek dimulai dari keputusan sederhana yang jelas.',
                'Belajar secara konsisten lebih efektif daripada terburu-buru.',
                'Ide kreatif sering muncul saat kita mencoba hal berbeda.',
                'Kebiasaan meninjau ulang pekerjaan dapat mengurangi kesalahan.',
                'Jadwal yang teratur membuat kegiatan sehari-hari lebih mudah.',
                'Kita dapat berkembang tanpa harus membandingkan diri dengan orang lain.',
                'Pertanyaan sederhana kadang membuka diskusi yang menarik.',
                'Hasil yang baik membutuhkan proses yang dilakukan dengan teliti.',
                'Mencatat perkembangan membantu kita memahami pola latihan.',
                'Keterampilan baru terasa sulit sebelum menjadi kebiasaan.',
                'Satu langkah kecil tetap membawa kita menuju tujuan.',
                'Perubahan positif dimulai dari tindakan yang dilakukan berulang.',
                'Setiap orang memiliki cara belajar yang berbeda.',
            ],
            'hard' => [
                'Periksa data sebelum menekan Simpan.',
                'Catat angka dengan lebih teliti.',
                'Pengumuman #24 berlaku mulai pukul 07.30, bukan pukul 08.00 besok.',
                'Rapat dimulai pukul 08.30; semua peserta wajib hadir tepat waktu.',
                'Periksa data pada kolom A, B, dan C sebelum menyimpan.',
                'Nomor pesanan #4821 telah diproses pada 12/08/2026.',
                'Catatan: utamakan ketelitian, lalu tingkatkan kecepatan!',
                'Laporan tersebut menunjukkan kenaikan sebesar 17,5% bulan ini.',
                'Ia bertanya, "Apakah berkas terakhir sudah disimpan?"',
                'Nama dokumen yang benar adalah laporan_akhir-v2.pdf.',
                'Sistem mengirim pemberitahuan pada pukul 21.15 WIB.',
                'Harga awal Rp24.500 berubah menjadi Rp29.750.',
                'Alamat surel contoh adalah nama@example.com.',
                'Kode keamanan memakai 3 simbol: @, #, dan !.',
                'Verifikasi 2 langkah membantu menjaga keamanan akun.',
                'Sebanyak 64 dari 100 peserta menyetujui usulan tersebut.',
                'Gunakan tombol Ctrl+S sebelum menutup dokumen.',
                'Versi 3.2.1 memperbaiki masalah pada rilis sebelumnya.',
                'Paket ini memiliki berat 1,25 kg termasuk kemasan.',
                'Nomor ruangan yang tepat adalah #07, bukan #70.',
                'Tunggu 5 detik, lalu pilih tombol bertuliskan "Lanjut".',
                'Panitia mencatat 3 prioritas: waktu, biaya, dan mutu.',
                'Perubahan 10% tidak selalu menghasilkan keuntungan yang sama.',
            ],
            'expert' => [
                'Validasi input sebelum menyimpan data.',
                'Periksa respons sebelum menangani kesalahan.',
                'Controller Laravel memeriksa input sebelum menyimpan perubahan ke dalam database.',
                'Dalam JavaScript, const siap = true; menyimpan nilai tetap.',
                'Fungsi validate(input) sebaiknya mengembalikan nilai boolean.',
                'Perintah SELECT id FROM users; mengambil data tertentu.',
                'Gunakan git status sebelum membuat commit baru.',
                'API mengembalikan {"ok":true,"count":12} setelah permintaan berhasil.',
                'Respons HTTP 404 menandakan sumber daya tidak ditemukan.',
                'Pastikan user?.profile?.name tersedia sebelum ditampilkan.',
                'Perintah npm run build menghasilkan aset untuk produksi.',
                'Alamat /api/users/42 dapat menunjuk satu pengguna.',
                'Jangan menyimpan password pengguna dalam teks biasa.',
                'Ekspresi items.map(item => item.id) mengambil daftar identitas.',
                'Struktur try { load(); } catch (err) { log(err); } menangani kesalahan.',
                'Objek JSON menggunakan tanda petik ganda untuk nama kunci.',
                'Kondisi count >= 10 memeriksa batas nilai tertentu.',
                'Indeks database dapat mempercepat proses pencarian data.',
                'Kesalahan 500 biasanya terjadi ketika server bermasalah.',
                'Jalankan php artisan migrate:status untuk memeriksa migrasi.',
                'Perhitungan total = harga * jumlah menghasilkan nilai akhir.',
                'Gunakan perbandingan ketat seperti nilai === null.',
                'Endpoint memerlukan token valid pada bagian header permintaan.',
            ],
        ],
    ];

    public static function build(
        string $source,
        string $language,
        string $difficulty,
        string $mode,
        int $target,
        int $seed
    ): string {
        $sentences = self::SENTENCES[$language][$difficulty] ?? [];
        $source = trim(preg_replace('/\s+/u', ' ', $source) ?? '');

        // An admin-added text becomes an additional candidate, not a template
        // repeatedly pasted until the entire prompt is filled.
        if ($source !== '' && mb_strlen($source) <= 180) {
            $sentences[] = $source;
        }

        $sentences = array_values(array_unique(array_filter($sentences)));

        if ($sentences === []) {
            return '';
        }

        if ($mode === 'words') {
            return self::wordPrompt($sentences, $target, $seed);
        }

        $desired = $mode === 'practice'
            ? 1800
            : match ($target) {
                15 => 320,
                30 => 520,
                60 => 880,
                120 => 1500,
                default => max(450, min(1500, $target * 14)),
            };

        $pieces = [];
        $length = 0;
        $cycle = 0;

        while ($length < $desired && $length < 4900) {
            $ordered = self::shuffle($sentences, $seed, $cycle++);

            foreach ($ordered as $sentence) {
                $increment = mb_strlen($sentence) + ($length > 0 ? 1 : 0);

                if ($length + $increment > 4950) {
                    break 2;
                }

                $pieces[] = $sentence;
                $length += $increment;

                if ($length >= $desired) {
                    break 2;
                }
            }
        }

        return implode(' ', $pieces);
    }

    /** Exactly N words, ending at a sentence boundary. */
    private static function wordPrompt(array $sentences, int $target, int $seed): string
    {
        $counts = array_map(static fn (string $line): int => self::wordCount($line), $sentences);
        $reachable = array_fill(0, $target + 1, false);
        $reachable[0] = true;

        for ($remaining = 1; $remaining <= $target; $remaining++) {
            foreach ($counts as $count) {
                if ($count <= $remaining && $reachable[$remaining - $count]) {
                    $reachable[$remaining] = true;
                    break;
                }
            }
        }

        if (!$reachable[$target]) {
            // A fallback keeps the previous backend behavior for custom admin
            // content that cannot form an exact word total with this library.
            $words = preg_split('/\s+/u', implode(' ', $sentences), -1, PREG_SPLIT_NO_EMPTY);
            return implode(' ', array_slice($words ?: [], 0, $target));
        }

        $result = [];
        $usage = array_fill(0, count($sentences), 0);
        $previous = -1;
        $remaining = $target;
        $step = 0;

        while ($remaining > 0) {
            $choices = [];

            foreach ($counts as $index => $count) {
                if ($count <= $remaining && $reachable[$remaining - $count]) {
                    $choices[] = $index;
                }
            }

            // Prefer unused sentences and avoid repeating the previous one.
            usort($choices, static function (int $a, int $b) use ($sentences, $seed, $step, $usage, $previous): int {
                $scoreA = $usage[$a] * 3 + ($a === $previous ? 2 : 0);
                $scoreB = $usage[$b] * 3 + ($b === $previous ? 2 : 0);

                if ($scoreA !== $scoreB) {
                    return $scoreA <=> $scoreB;
                }

                return strcmp(
                    hash('sha256', "$seed:$step:{$sentences[$a]}"),
                    hash('sha256', "$seed:$step:{$sentences[$b]}")
                );
            });

            $selected = $choices[0];
            $result[] = $sentences[$selected];
            $remaining -= $counts[$selected];
            $usage[$selected]++;
            $previous = $selected;
            $step++;
        }

        return implode(' ', $result);
    }

    private static function shuffle(array $sentences, int $seed, int $cycle): array
    {
        usort($sentences, static fn (string $a, string $b): int => strcmp(
            hash('sha256', "$seed:$cycle:$a"),
            hash('sha256', "$seed:$cycle:$b")
        ));

        return $sentences;
    }

    private static function wordCount(string $text): int
    {
        return count(preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: []);
    }
}
