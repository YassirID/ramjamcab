<?php

function getTotalPeserta($conn, $role, $user_id) {
    if ($role === 'admin' || $role === 'cabang') {
        $sql = "SELECT COUNT(*) AS total FROM peserta";
    } else {
        $sql = "SELECT COUNT(*) AS total FROM peserta WHERE user_id = '$user_id'";
    }
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0) ? $result->fetch_assoc()['total'] : 0;
}

function getTotalPesertaTerverifikasi($conn, $role, $user_id) {
    $kondisi = "pas_foto_path IS NOT NULL AND kta_path IS NOT NULL AND asuransi_path IS NOT NULL AND sertifikat_sfh_path IS NOT NULL";

    if ($role === 'admin' || $role === 'cabang') {
        $sql = "SELECT COUNT(*) AS total FROM peserta WHERE $kondisi";
    } else {
        $sql = "SELECT COUNT(*) AS total FROM peserta WHERE user_id = '$user_id' AND $kondisi";
    }

    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0) ? $result->fetch_assoc()['total'] : 0;
}

function getTotalPembayaran($conn, $role, $user_id) {
    if ($role === 'admin' || $role === 'cabang') {
        $sql = "SELECT SUM(nominal) AS total FROM bukti_pembayaran";
    } else {
        $sql = "SELECT SUM(nominal) AS total FROM bukti_pembayaran WHERE user_id = '$user_id'";
    }
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0) ? $result->fetch_assoc()['total'] : 0;
}



function isUploader($role) {
    return in_array($role, ['utara', 'selatan', 'tengah', 'saka', 'admin']);
}

