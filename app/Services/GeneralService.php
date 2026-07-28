<?php

namespace App\Services;

use App\Helpers\General;
use App\Models\ContactMessages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GeneralService
{
    /**
     * Get user monthly chart data.
     */
    public function getUserMonthlyChartData(): array
    {
        $monthList = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $month = date('n');
        $label = [];
        $labelIndex = [];
        for ($i = 1; $i < 13; $i++) {
            $mi = ($month + $i) % 12;
            $labelIndex[] = $mi;
            $label[] = $monthList[($mi ? $mi : 12) - 1];
        }

        $data = [];
        $result = DB::select('SELECT count(*) as total, MONTH(FROM_UNIXTIME(created_at)) as month FROM `user` WHERE role=2 GROUP BY MONTH(FROM_UNIXTIME(created_at));');
        foreach ($labelIndex as $i) {
            $monthData = 0;
            foreach ($result as $r) {
                if ($r->month == $i) {
                    $monthData = $r->total;
                }
            }
            $data[] = $monthData;
        }

        return [
            'label' => $label,
            'data' => $data,
        ];
    }

    /**
     * Get user data for the last 6 months for chart.
     */
    public function getUserLast6MonthsChartData(): array
    {
        $monthList = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $label = [];
        $data = [];

        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');

        for ($i = 5; $i >= 0; $i--) {
            $monthIndex = ($currentMonth - $i - 1 + 12) % 12;
            $year = $currentYear;
            if ($monthIndex >= $currentMonth) {
                $year -= 1;
            }

            $label[] = $monthList[$monthIndex].' '.$year;
            $monthData = 0;

            $result = DB::select("SELECT count(*) as total FROM `user` WHERE role = 2 AND YEAR(FROM_UNIXTIME(created_at)) = $year AND MONTH(FROM_UNIXTIME(created_at)) = $monthIndex + 1");

            if (! empty($result)) {
                $monthData = $result[0]->total;
            }

            $data[] = $monthData;
        }

        return [
            'label' => array_reverse($label),
            'data' => array_reverse($data),
        ];
    }

    /**
     * Get user data for the last 7 days for chart.
     */
    public function getUserLast7DaysChartData(): array
    {
        $dayList = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $labels = [];
        $data = array_fill(0, 7, 0);
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = $dayList[date('w', strtotime($date))];
        }

        $startDate = date('Y-m-d', strtotime('-6 days'));
        $endDate = date('Y-m-d');

        $result = DB::select("
        SELECT COUNT(*) AS total, DATE(FROM_UNIXTIME(created_at)) AS date
        FROM `user`
        WHERE role IN (4) AND DATE(FROM_UNIXTIME(created_at)) BETWEEN '$startDate' AND '$endDate'
        GROUP BY DATE(FROM_UNIXTIME(created_at))
    ");

        foreach ($result as $row) {
            $index = 6 - (strtotime($row->date) - strtotime($startDate)) / (60 * 60 * 24);
            if ($index >= 0 && $index < 7) {
                $data[$index] = $row->total;
            }
        }

        return [
            'label' => array_reverse($labels),
            'data' => array_reverse($data),
        ];
    }

    public function contactProcess($postData)
    {

        // Check reCAPTCHA validation
        $general = new General;
        if ($general->rateLimit('contact')) {
            return ['status' => 0, 'message' => 'Too many attempts, please try again later.'];
        }

        // if ($general->recaptchaFails()) {
        //     return ['status' => 0, 'message' => 'Please check reCAPTCHA.'];
        // }

        $validator = Validator::make($postData, [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'regex:/^[\w\.\-]+@[a-zA-Z\d\-]+\.[a-zA-Z]{2,}$/', // Ensure email contains a dot
            ],
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ], [
            'email.regex' => 'Please enter a valid email address.',
        ]);

        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }

        /* SAVE CONTACT MESSAGE TO DATABASE */

        ContactMessages::create([
            'user_id' => auth()->id(),
            'email' => $postData['email'],
            'subject' => $postData['subject'],
            'message' => $postData['message'],
        ]);

        /* SEND EMAIL TO ADMIN */
        $general->sendEmail(config('setting.admin_email'), 'admin_contact', $postData);

        return ['status' => 1, 'message' => 'Submit request successfully', 'next' => 'refresh'];
    }
}
