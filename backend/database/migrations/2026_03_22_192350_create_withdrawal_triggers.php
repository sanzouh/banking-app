<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // TRIGGER INSERT
        DB::unprepared('
            CREATE TRIGGER after_withdrawal_insert
            AFTER INSERT ON withdrawals
            FOR EACH ROW
            BEGIN
                DECLARE username VARCHAR(255);
                SELECT name INTO username FROM users WHERE id_user = NEW.user_id;

                INSERT INTO withdrawals_audit (
                    action_type, withdraw_num, account_num,
                    client_name, old_amount, new_amount, user
                )
                VALUES (
                    "INSERT", NEW.withdraw_num, NEW.account_num,
                    (SELECT name FROM clients WHERE account_num = NEW.account_num),
                    NULL, NEW.amount, username
                );

                UPDATE clients
                SET balance = balance - NEW.amount
                WHERE account_num = NEW.account_num;
            END
        ');

        // TRIGGER UPDATE
        DB::unprepared('
            CREATE TRIGGER after_withdrawal_update
            AFTER UPDATE ON withdrawals
            FOR EACH ROW
            BEGIN
                DECLARE username VARCHAR(255);
                SELECT name INTO username FROM users WHERE id_user = NEW.user_id;

                INSERT INTO withdrawals_audit (
                    action_type, withdraw_num, account_num,
                    client_name, old_amount, new_amount, user
                )
                VALUES (
                    "UPDATE", NEW.withdraw_num, NEW.account_num,
                    (SELECT name FROM clients WHERE account_num = NEW.account_num),
                    OLD.amount, NEW.amount, username
                );

                UPDATE clients
                SET balance = balance + OLD.amount - NEW.amount
                WHERE account_num = NEW.account_num;
            END
        ');


        // TRIGGER DELETE
        DB::unprepared('
            CREATE TRIGGER after_withdrawal_delete
            AFTER DELETE ON withdrawals
            FOR EACH ROW
            BEGIN
                DECLARE username VARCHAR(255);
                SELECT name INTO username FROM users WHERE id_user = OLD.user_id;

                INSERT INTO withdrawals_audit (
                    action_type, withdraw_num, account_num,
                    client_name, old_amount, new_amount, user
                )
                VALUES (
                    "DELETE", OLD.withdraw_num, OLD.account_num,
                    (SELECT name FROM clients WHERE account_num = OLD.account_num),
                    OLD.amount, NULL, username
                );

                UPDATE clients
                SET balance = balance + OLD.amount
                WHERE account_num = OLD.account_num;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_withdrawal_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS after_withdrawal_update');
        DB::unprepared('DROP TRIGGER IF EXISTS after_withdrawal_delete');
    }
};
