<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RenameReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('meetings_attendees')) {
            Schema::rename('meetings_attendees', 'reservations');
        }
        // handle edge case when someone executed the migration on the feature branch
        if(Schema::hasTable('attends')) {
            Schema::rename('meetings_attendees', 'reservations');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasTable('reservations')) {
            Schema::rename('reservations', 'meetings_attendees');
        }
        // handle edge case when someone executed the migration on the feature branch
        // roll all the way back to initial state
        if(Schema::hasTable('attends')) {
            Schema::rename('reservations', 'meetings_attendees');
        }
    }
}
