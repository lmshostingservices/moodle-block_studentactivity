<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_block_studentactivity_upgrade($oldversion) {
    if ($oldversion < 2026073100) {
        upgrade_block_savepoint(true, 2026073100, 'studentactivity');
    }
    return true;
}
