<?php
function canEdit($role) {
    return in_array($role, ['selatan', 'tengah', 'utara', 'saka', 'admin']);
}
function canViewAll($role) {
    return in_array($role, ['cabang', 'admin']);
}
