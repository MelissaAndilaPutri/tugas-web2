<?php
include 'config.php';

// Function to handle SQL errors
function handleError($query) {
    global $conn;
    if (!$query) {
        die("Error: " . mysqli_error($conn));
    }
}

// Handle form submissions for books
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add Book
    if (isset($_POST['add_book'])) {
        $judul = mysqli_real_escape_string($conn, $_POST['judul']);
        $penulis = mysqli_real_escape_string($conn, $_POST['penulis']);
        $tahun_terbit = (int)$_POST['tahun_terbit'];
        $stok = (int)$_POST['stok'];

        $sql = "INSERT INTO buku (judul, penulis, tahun_terbit, stok) VALUES ('$judul', '$penulis', $tahun_terbit, $stok)";
        handleError(mysqli_query($conn, $sql));
    }

    // Update Book
    if (isset($_POST['update_book'])) {
        $id_buku = (int)$_POST['id_buku'];
        $judul = mysqli_real_escape_string($conn, $_POST['judul']);
        $penulis = mysqli_real_escape_string($conn, $_POST['penulis']);
        $tahun_terbit = (int)$_POST['tahun_terbit'];
        $stok = (int)$_POST['stok'];

        $sql = "UPDATE buku SET judul='$judul', penulis='$penulis', tahun_terbit=$tahun_terbit, stok=$stok WHERE id=$id_buku";
        handleError(mysqli_query($conn, $sql));
    }

    // Add Borrowing
    if (isset($_POST['borrow_book'])) {
        $id_buku = (int)$_POST['id_buku'];
        $nama_anggota = mysqli_real_escape_string($conn, $_POST['nama_anggota']);
        $tanggal_peminjaman = mysqli_real_escape_string($conn, $_POST['tanggal_peminjaman']);

        $sql = "INSERT INTO peminjaman (id_buku, nama_anggota, tanggal_peminjaman, status) VALUES ($id_buku, '$nama_anggota', '$tanggal_peminjaman', 'dipinjam')";
        handleError(mysqli_query($conn, $sql));
    }

    // Update Borrowing
    if (isset($_POST['update_borrowing'])) {
        $id_peminjaman = (int)$_POST['id_peminjaman'];
        $id_buku = (int)$_POST['id_buku'];
        $nama_anggota = mysqli_real_escape_string($conn, $_POST['nama_anggota']);
        $tanggal_pengembalian = mysqli_real_escape_string($conn, $_POST['tanggal_pengembalian']);

        $sql = "UPDATE peminjaman SET id_buku=$id_buku, nama_anggota='$nama_anggota', tanggal_pengembalian='$tanggal_pengembalian', status='dikembalikan' WHERE id_peminjam=$id_peminjaman";
        handleError(mysqli_query($conn, $sql));
    }
}

// Handle delete for books
if (isset($_GET['delete_book'])) {
    $id_buku = (int)$_GET['delete_book'];
    $sql = "DELETE FROM buku WHERE id=$id_buku";
    handleError(mysqli_query($conn, $sql));
}

// Handle delete for borrowings
if (isset($_GET['delete_borrowing'])) {
    $id_peminjaman = (int)$_GET['delete_borrowing'];
    $sql = "DELETE FROM peminjaman WHERE id_peminjam=$id_peminjaman";
    handleError(mysqli_query($conn, $sql));
}

// Fetch data
$books = mysqli_query($conn, "SELECT * FROM buku");
$borrowings = mysqli_query($conn, "SELECT * FROM peminjaman");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Perpustakaan</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Manajemen Perpustakaan</h1>

    <h2>Tambah Buku</h2>
    <form method="post">
        <input type="text" name="judul" placeholder="Judul" required>
        <input type="text" name="penulis" placeholder="Penulis" required>
        <input type="number" name="tahun_terbit" placeholder="Tahun Terbit" required>
        <input type="number" name="stok" placeholder="Stok" required>
        <button type="submit" name="add_book">Tambah Buku</button>
    </form>

    <h2>Daftar Buku</h2>
    <table border="1">
        <tr>
            <th>ID Buku</th>
            <th>Judul Buku</th>
            <th>Penulis</th>
            <th>Tahun Terbit</th>
            <th>Stok</th>
            <th>Aksi</th>
        </tr>
        
        <?php while ($row = mysqli_fetch_assoc($books)) { ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['judul']) ?></td>
                <td><?= htmlspecialchars($row['penulis']) ?></td>
                <td><?= htmlspecialchars($row['tahun_terbit']) ?></td>
                <td><?= htmlspecialchars($row['stok']) ?></td>
                <td>
                    <a href="?edit_book=<?= $row['id'] ?>">Edit</a>
                    <a href="?delete_book=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus?');">Hapus</a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <?php if (isset($_GET['edit_book'])) {
        $id_buku = (int)$_GET['edit_book'];
        $sql = "SELECT * FROM buku WHERE id=$id_buku";
        $editResult = mysqli_query($conn, $sql);
        $editRow = mysqli_fetch_assoc($editResult);
    ?>
    <h2>Edit Buku</h2>
    <form method="post">
        <input type="hidden" name="id_buku" value="<?= htmlspecialchars($editRow['id']) ?>">
        <input type="text" name="judul" value="<?= htmlspecialchars($editRow['judul']) ?>" required>
        <input type="text" name="penulis" value="<?= htmlspecialchars($editRow['penulis']) ?>" required>
        <input type="number" name="tahun_terbit" value="<?= htmlspecialchars($editRow['tahun_terbit']) ?>" required>
        <input type="number" name="stok" value="<?= htmlspecialchars($editRow['stok']) ?>" required>
        <button type="submit" name="update_book">Update Buku</button>
    </form>
    <?php } ?>
    <h2>Tambah Peminjaman</h2>
<form method="post">
    <select name="id_buku" required>
        <option value="">Pilih Buku</option>
        <?php
        // Mengambil daftar buku dari database
        $booksForBorrowing = mysqli_query($conn, "SELECT * FROM buku");
        while ($book = mysqli_fetch_assoc($booksForBorrowing)) {
            echo "<option value=\"{$book['id']}\">" . htmlspecialchars($book['judul']) . " - " . htmlspecialchars($book['penulis']) . "</option>";
        }
        ?>
    </select>
    <input type="text" name="nama_anggota" placeholder="Nama Anggota" required>
    <input type="date" name="tanggal_peminjaman" required>
    <button type="submit" name="borrow_book">Pinjam Buku</button>
</form>

    <h2>Daftar Peminjaman</h2>
    <table border="1">
        <tr>
            <th>ID Peminjaman</th>
            <th>Nama Anggota</th>
            <th>ID Buku</th>
            <th>Tanggal Peminjaman</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($borrowings)) { ?>
            <tr>
                <td><?= htmlspecialchars($row['id_peminjam']) ?></td>
                <td><?= htmlspecialchars($row['nama_anggota']) ?></td>
                <td><?= htmlspecialchars($row['id_buku']) ?></td>
                <td><?= htmlspecialchars($row['tanggal_peminjaman']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td>
                    <a href="?delete_borrowing=<?= $row['id_peminjam'] ?>" onclick="return confirm('Yakin ingin menghapus?');">Hapus</a>
                    <?php if ($row['status'] == 'dipinjam') { ?>
                        <a href="?edit_borrowing=<?= $row['id_peminjam'] ?>">Kembalikan</a>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>

    <?php if (isset($_GET['edit_borrowing'])) {
        $id_peminjaman = (int)$_GET['edit_borrowing'];
        $sql = "SELECT * FROM peminjaman WHERE id_peminjam=$id_peminjaman";
        $editResult = mysqli_query($conn, $sql);
        $editRow = mysqli_fetch_assoc($editResult);
    ?>
    <h2>Kembalikan Buku</h2>
    <form method="post">
        <input type="hidden" name="id_peminjaman" value="<?= htmlspecialchars($editRow['id_peminjam']) ?>">
        <input type="text" name="nama_anggota" placeholder="Nama Anggota" required>

        <select name="id_buku" required>
            <option value="<?= htmlspecialchars($editRow['id_buku']) ?>"><?= htmlspecialchars($editRow['id_buku']) ?></option>
        </select>
        <input type="date" name="tanggal_pengembalian" required>
        <button type="submit" name="update_borrowing">Kembalikan Buku</button>
    </form>
    <?php } ?>

</body>
</html>
