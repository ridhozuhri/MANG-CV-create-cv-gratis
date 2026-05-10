<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'session_token' => ['type' => 'CHAR', 'constraint' => 36],
            'fingerprint_hash' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45],
            'user_agent_hash' => ['type' => 'VARCHAR', 'constraint' => 64],
            'current_step' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            'selected_template' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'classic'],
            'is_flagged' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'flag_reason' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'pdf_generated_count' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'last_activity_at' => ['type' => 'DATETIME'],
            'expires_at' => ['type' => 'DATETIME'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('session_token');
        $this->forge->addKey('expires_at');
        $this->forge->addKey('fingerprint_hash');
        $this->forge->createTable('cv_sessions', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'session_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'section_name' => ['type' => 'VARCHAR', 'constraint' => 20],
            'data_json' => ['type' => 'MEDIUMTEXT'],
            'data_hash' => ['type' => 'VARCHAR', 'constraint' => 32],
            'character_count' => ['type' => 'MEDIUMINT', 'unsigned' => true, 'default' => 0],
            'updated_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['session_id', 'section_name']);
        $this->forge->addKey('session_id');
        $this->forge->addForeignKey('session_id', 'cv_sessions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('cv_data', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'session_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'export_format' => ['type' => 'ENUM', 'constraint' => ['pdf', 'txt', 'json']],
            'template_name' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'content_hash' => ['type' => 'VARCHAR', 'constraint' => 32],
            'cache_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'file_size_bytes' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'generation_time_ms' => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45],
            'was_cached' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('session_id');
        $this->forge->addKey('created_at');
        $this->forge->addKey('content_hash');
        $this->forge->addForeignKey('session_id', 'cv_sessions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('export_logs', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'key_identifier' => ['type' => 'VARCHAR', 'constraint' => 64],
            'action_name' => ['type' => 'VARCHAR', 'constraint' => 50],
            'hit_count' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'window_start' => ['type' => 'INT', 'unsigned' => true],
            'window_duration_seconds' => ['type' => 'SMALLINT', 'unsigned' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45],
            'last_hit_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['key_identifier', 'action_name', 'window_start']);
        $this->forge->addKey('window_start');
        $this->forge->createTable('rate_limits', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'session_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45],
            'action_attempted' => ['type' => 'VARCHAR', 'constraint' => 100],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 500],
            'request_data' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('session_id', 'cv_sessions', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('abuse_reports', true);
    }

    public function down()
    {
        $this->forge->dropTable('abuse_reports', true);
        $this->forge->dropTable('rate_limits', true);
        $this->forge->dropTable('export_logs', true);
        $this->forge->dropTable('cv_data', true);
        $this->forge->dropTable('cv_sessions', true);
    }
}
