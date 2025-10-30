<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BusinessUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('business_unit')->insert([
            [
                'bu_id' => '13',
                'dashboard_path' => 'bilarbreeder',
                'name' => 'Bilar Breeder AR',
                'database' => 'bilar_breeder_ar',
                'host' => '127.0.0.1',
                'port' => '3306',
                'username' => 'root',
                'password' => '',
                'business_unit' => 'BILAR BREEDER',
                'business_unit_code' => 'BRDR BIL-AG003',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'bu_id' => '50',
                'dashboard_path' => 'gpjagna',
                'name' => 'Gp Jagna AR',
                'database' => 'gp_jagna_ar',
                'host' => '127.0.0.1',
                'port' => '3306',
                'username' => 'root',
                'password' => '',
                'business_unit' => 'GP FARM JAGNA',
                'business_unit_code' => 'GPFARM-AG021',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'bu_id' => '43',
                'dashboard_path' => 'rizalbreeder',
                'name' => 'Rizal Breeder AR',
                'database' => 'rizal_breeder_live',
                'host' => '172.16.217.11',
                'port' => '3306',
                'username' => 'RizalBreederLive',
                'password' => 'FarMsTeaM',
                'business_unit' => 'BILAR RIZAL BREEDER',
                'business_unit_code' => 'RIZAL BIL-AG015',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'bu_id' => '16',
                'dashboard_path' => 'lapsaonbreeder',
                'name' => 'Lapsaon Breeder AR',
                'database' => 'lapsaon_breeder_ar',
                'host' => '127.0.0.1',
                'port' => '3306',
                'username' => 'root',
                'password' => '',
                'business_unit' => 'DIMIAO LAPSAON',
                'business_unit_code' => 'BRDR DIMLAP-AG006',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'bu_id' => '14',
                'dashboard_path' => 'hatchery',
                'name' => 'Hatchery AR',
                'database' => 'hatchery_ar',
                'host' => '127.0.0.1',
                'port' => '3306',
                'username' => 'root',
                'password' => '',
                'business_unit' => 'BILAR HATCHERY',
                'business_unit_code' => 'HTCH BIL-AG004',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'bu_id' => '11',
                'dashboard_path' => 'cortespiggery',
                'name' => 'Cortes Piggery AR',
                'database' => 'cortes_piggery_ar',
                'host' => '127.0.0.1',
                'port' => '3306',
                'username' => 'root',
                'password' => '',
                'business_unit' => 'PIGGERY CORTES',
                'business_unit_code' => 'PGRY CORT-AG001',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'bu_id' => '12',
                'dashboard_path' => 'cortespoultry',
                'name' => 'Cortes Poultry AR',
                'database' => 'cortes_poultry_ar',
                'host' => '127.0.0.1',
                'port' => '3306',
                'username' => 'root',
                'password' => '',
                'business_unit' => 'POULTRY CORTES',
                'business_unit_code' => 'LAYER CORT-AG002',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'bu_id' => '15',
                'dashboard_path' => 'canhayuponbreeder',
                'name' => 'Canhayupon Breeder AR',
                'database' => 'canhayupon_breeder_ar',
                'host' => '127.0.0.1',
                'port' => '3306',
                'username' => 'root',
                'password' => '',
                'business_unit' => 'DIMIAO CANHAYUPON',
                'business_unit_code' => 'BRDR DIMCAN-AG005',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
