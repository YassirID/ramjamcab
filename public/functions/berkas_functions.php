<?php
function getFilteredBerkas($conn, $role, $user_id, $kontingen_filter = null) {
    if ($role === 'admin' || $role === 'cabang') {
        $sql = "SELECT b.*, u.username FROM berkas_kontingen b 
                JOIN users u ON b.user_id = u.id";
        if ($kontingen_filter && $kontingen_filter !== 'all') {
            $sql .= " WHERE u.role = '$kontingen_filter'";
        }
    } else {
        $sql = "SELECT b.*, u.username FROM berkas_kontingen b 
                JOIN users u ON b.user_id = u.id 
                WHERE b.user_id = '$user_id'";
    }
    return $conn->query($sql);
}
