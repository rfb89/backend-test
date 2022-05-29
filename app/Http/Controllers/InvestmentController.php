<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Investment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvestmentController extends Controller
{
    /**
     * Get the age of an investment
     * @param  string  $date_start
     * @param  string  $date_end
     * @return \DateInterval
     */
    private function getAgeInvestment($date_start, $date_end): \DateInterval
    {
        $dateStart = new \DateTime(substr($date_start, 0, 10));
        $dateEnd   = new \DateTime(substr($date_end, 0, 10));

        $dateinterval = $dateStart->diff($dateEnd);

        return $dateinterval;
    }


    /**
     * Get calculated earnings from an investment
     * @param  float          $amount
     * @param  \DateInterval  $dateinterval
     * @return float
     */
    private function getCalculatedEarning($amount, \DateInterval $dateinterval): float
    {
        $earning   = $amount;
        $gain_rate = $this->getGainRate();
        $mounths   = ($dateinterval->format('%y') * 12) + $dateinterval->format('%m');

        for ($i = 0; $i < $mounths; $i++) {
            $earning += $earning * $gain_rate;
        }

        $earning -= $amount;

        return $earning;
    }


    /**
     * Get the gain rate
     * @return float
     */
    private function getGainRate()
    {
        $gain_rate = 0.0052; // 0,52%
        return $gain_rate;
    }


    /**
     * Get the investments with gain rate and tax rate
     * @param  string $date_end
     */
    private function getInvestmentsAfterRates($investments, $date_end)
    {
        if ($investments instanceof \App\Models\Investment) {
            if (!empty($investments->withdrawal_date)) {
                return $investments;
            }

            $dateinterval = $this->getAgeInvestment($investments->date, $date_end);
            $investments->withdrawal_gain    = $this->getCalculatedEarning($investments->amount, $dateinterval);
            $investments->withdrawal_tax     = $investments->withdrawal_gain * $this->getTaxRate($dateinterval);
            $investments->withdrawal_balance = $investments->amount + $investments->withdrawal_gain - $investments->withdrawal_tax;

            return $investments;
        }

        $investments = $investments->map(function ($investment) use ($date_end) {
            if (!empty($investment->withdrawal_date)) {
                return $investment;
            }

            $dateinterval = $this->getAgeInvestment($investment->date, $date_end);
            $investment->withdrawal_gain    = $this->getCalculatedEarning($investment->amount, $dateinterval);
            $investment->withdrawal_tax     = $investment->withdrawal_gain * $this->getTaxRate($dateinterval);
            $investment->withdrawal_balance = $investment->amount + $investment->withdrawal_gain - $investment->withdrawal_tax;

            return $investment;
        });

        return $investments;
    }


    /**
     * Get the lastest investments from an user
     * @param  int  $user_id
     * @return Collection
     */
    private function getInvestmentsByUser($user_id, $offset = 0, $limit = 0)
    {
        $investments = DB::table('investments')
            ->where('user_id', $user_id)
            ->offset($offset)
            ->limit($limit + 1)
            ->orderBy('id')
            ->get();

        return $investments;
    }


    /**
     * Get the tax rate according to time invested
     * @param  \DateInterval  $dateinterval
     * @return float
     */
    private function getTaxRate($dateinterval)
    {
        $tax = 0;

        // If it is less than one year old, the percentage will be 22.5%
        if ($dateinterval->format('%y') < 1) {
            $tax = 22.5;
        }

        // If it is between one and two years old, the percentage will be 18.5%
        elseif ($dateinterval->format('%y') < 2) {
            $tax = 18.5;
        }

        // If older than two years, the percentage will be 15%
        else {
            $tax = 15;
        }

        $tax /= 100;

        return $tax;
    }


    /**
     * Store a newly created resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'amount'  => 'required|numeric|min:0|not_in:0',
            'date'    => 'required|date|date_format:Y-m-d|before_or_equal:now',
        ]);

        if ($validator->fails()) {
            return response()
                ->json([
                    'status'   => false,
                    'messages' => $validator->errors()->all(),
                ]);
        }

        $validated  = $validator->validated();
        $investment = Investment::create($validated);

        return response()->json([
            'status'  => true,
            'message' => trans('global.record-created-successfully'),
            'data'    => $investment,
        ], 201);
    }


    /**
     * Remove the specified resource from storage.
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request, $user_id): JsonResponse
    {
        try {
            User::findOrFail($user_id);
        } catch (\Throwable $th) {
            return response()
                ->json([
                    'status'  => false,
                    'message' => trans('global.user-not-found', [
                        'id' => $user_id,
                    ]),
                ]);
        }

        $limit  = 10;
        $page   = $request->page ? $request->page - 1 : 0;
        $offset = $page * $limit;

        $investments = $this->getInvestmentsByUser($user_id, $offset, $limit);
        $has_next    = $investments->count() > $limit;

        if ($has_next) {
            $investments->pop();
        }

        $date_end    = date('Y-m-d');
        $investments = $this->getInvestmentsAfterRates($investments, $date_end);

        return response()->json([
            'status'     => true,
            'pagination' => [
                'page'     => $page + 1,
                'has_next' => $has_next,
            ],
            'investments' => $investments,
        ]);
    }


    /**
     * Display the specified resource.
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $investment = Investment::findOrFail($id);
        } catch (\Throwable $th) {
            return response()
                ->json([
                    'status'  => false,
                    'message' => trans('investment.investment-not-found'),
                ]);
        }

        if (empty($investment->withdrawal_date)) {
            $date_end = date('Y-m-d');

            $calculated = $this->getInvestmentsAfterRates($investment, $date_end);

            $investment->withdrawal_gain    = $calculated->withdrawal_gain;
            $investment->withdrawal_tax     = $calculated->withdrawal_tax;
            $investment->withdrawal_balance = $calculated->withdrawal_balance;
        }

        return response()->json([
            'status' => true,
            'data'   => $investment,
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdrawal(Request $request, $id): JsonResponse
    {
        try {
            $investment = Investment::findOrFail($id);
        } catch (\Throwable $th) {
            return response()
                ->json([
                    'status'  => false,
                    'message' => trans('investment.investment-not-found'),
                ]);
        }

        if (!empty($investment->withdrawal_date)) {
            return response()
                ->json([
                    'status'  => false,
                    'message' => trans('investment.the-investment-has-already-been-withdrawn'),
                ]);
        }

        $validator = Validator::make($request->all(), [
            'date' => sprintf('date|date_format:Y-m-d|before_or_equal:now|after_or_equal:%s', $investment->date),
        ]);

        if ($validator->fails()) {
            return response()
                ->json([
                    'status'   => false,
                    'messages' => $validator->errors()->all(),
                ]);
        }

        $date_end = $request->date ?? date('Y-m-d');

        $calculated = $this->getInvestmentsAfterRates($investment, $date_end);

        $investment->withdrawal_date    = $date_end;
        $investment->withdrawal_gain    = $calculated->withdrawal_gain;
        $investment->withdrawal_tax     = $calculated->withdrawal_tax;
        $investment->withdrawal_balance = $calculated->withdrawal_balance;
        $investment->save();

        return response()->json([
            'status'  => true,
            'message' => trans('global.record-updated-successfully'),
            'data'    => $investment,
        ], 200);
    }
}
