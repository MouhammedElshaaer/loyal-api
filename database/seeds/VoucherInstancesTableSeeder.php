<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Models\VoucherInstancePoints;
use App\Models\Voucher;

class VoucherInstancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $connection = 'mysql_testing';
        factory(App\Models\VoucherInstance::class, intval(config('constants.testing.voucher_instances_number')))
            ->make()
            ->each(function ($model) use($connection) {
                $model->setConnection($connection);
                $model->save();

                $voucher = Voucher::find($model->voucher_id);
                $user = User::find($model->user_id);

                $voucherPoints = $voucher->points;
                foreach ($user->transactions as $transaction) {

                    $transactionPoints = $transaction->transactionPoints;
                    if ($voucherPoints > 0 && $transactionPoints->is_valid) {

                        $availablePoints = $transactionPoints->available_points;
                        $neededPoints = null;

                        if ($voucherPoints<$availablePoints) { $neededPoints = $voucherPoints; }
                        else {
                            $neededPoints = $availablePoints;
                            $transactionPoints->used_at = \Carbon\Carbon::now();
                        }

                        if ($neededPoints) {
                            $voucherPoints -= $neededPoints;
                            $transactionPoints->redeemed += $neededPoints;
                            $transactionPoints->save();
                            $viPointsAttributes = [
                                'voucher_instance_id' => $model->id,
                                'transaction_points_id' => $transactionPoints->id,
                                'amount' => $neededPoints,
                            ];
                            $voucherInstancePoints = VoucherInstancePoints::create($viPointsAttributes);
                        } else { throw new Exception('Failed create voucherInstancePoints'); }
                    }

                }
        });
    }
}
