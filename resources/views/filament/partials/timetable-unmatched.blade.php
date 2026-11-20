@if (count($unmatched) > 0)
<x-filament::section heading="Jadwal di luar jam pelajaran atau hari sekolah (periksa data)">
    <p style="font-size: .875rem; margin-bottom: .5rem;">
        Jadwal berikut tidak tampil di tabel karena jam atau harinya tidak cocok dengan jam pelajaran
        yang ditetapkan. Perbaiki lewat menu Jadwal.
    </p>
    <div style="overflow-x: auto;">
        <table class="timetable">
            <thead>
                <tr>
                    <th>Kelas</th>
                    <th>Hari</th>
                    <th>Jam</th>
                    <th>Guru</th>
                    <th>Mata pelajaran</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($unmatched as $u)
                <tr>
                    <td>{{ $u['class'] }}</td>
                    <td>{{ $u['day'] }}</td>
                    <td>{{ $u['time'] }}</td>
                    <td>{{ $u['teacher'] }}</td>
                    <td>{{ $u['subject'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-filament::section>
@endif