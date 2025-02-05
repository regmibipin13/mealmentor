<?php

namespace App\Http\Controllers\Restaurants;

use App\Http\Controllers\Controller;
use App\Models\OnlineOrder;
use App\Models\PosOrder;
use App\Models\Restaurant;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use LaravelDaily\LaravelCharts\Classes\LaravelChart;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $orders = (new OnlineOrder)->newQuery();
        $posOrders = (new PosOrder)->newQuery();

        $ordersCount = $orders->count();
        $posOrdersCount = $posOrders->count();

        $orders = $orders->get();


        // Total Order Reports
        $chart_options = [
            'chart_title' => 'Orders',
            'report_type' => 'group_by_date',
            'model' => 'App\Models\OnlineOrder',
            'group_by_field' => 'date',
            'chart_type' => 'line',
            'filter_field' => 'date',
            'range_date_start' => Carbon::parse($request->from_date) ?? '',
            'range_date_end' => Carbon::parse($request->to_date),
            'group_by_period' => $request->report_type ?? 'day',
            'chart_height' => '200px',
            'aggregate_function'  => 'count',
            'date_format' => 'Y M d'
        ];
        $order_reports = new LaravelChart($chart_options);


        // Total Order Values
        $chart_options = [
            'chart_height' => '200px',
            'chart_title' => 'Total Sales',
            'report_type' => 'group_by_date',
            'model' => 'App\Models\OnlineOrder',
            'group_by_field' => 'date',
            'chart_type' => 'bar',
            'filter_field' => 'date',
            'range_date_start' => Carbon::parse($request->from_date) ?? '',
            'range_date_end' => Carbon::parse($request->to_date),
            'group_by_period' => $request->report_type ?? 'day',
            'aggregate_function' => 'sum',
            'aggregate_field' => 'payable_amount',
            'date_format' => 'Y M d'
        ];
        $sales_report = new LaravelChart($chart_options);


        // Total Order By PM
        $chart_options = [
            'chart_height' => '200px',
            'chart_title' => 'Orders By Payment Method',
            'report_type' => 'group_by_string',
            'model' => 'App\Models\OnlineOrder',
            'group_by_field' => 'payment_method',
            'chart_type' => 'pie',
            'filter_field' => 'date',
            'range_date_start' => Carbon::parse($request->from_date) ?? '',
            'range_date_end' => Carbon::parse($request->to_date),
            'group_by_period' => $request->report_type ?? 'day',
            'aggregate_function' => 'count',
            'date_format' => 'Y M d'
        ];
        $order_by_payment_method = new LaravelChart($chart_options);

        // Total Order BY STATUS
        $chart_options = [
            'chart_height' => '200px',
            'chart_title' => 'Orders By Order Status',
            'report_type' => 'group_by_string',
            'model' => 'App\Models\OnlineOrder',
            'group_by_field' => 'order_status',
            'chart_type' => 'pie',
            'filter_field' => 'date',
            'range_date_start' => Carbon::parse($request->from_date) ?? '',
            'range_date_end' => Carbon::parse($request->to_date),
            'group_by_period' => $request->report_type ?? 'day',
            'aggregate_function' => 'count',
            'date_format' => 'Y M d'
        ];
        $order_by_status = new LaravelChart($chart_options);

        // Total POS Order Reports
        $chart_options = [
            'chart_title' => 'POS Orders',
            'report_type' => 'group_by_date',
            'model' => 'App\Models\PosOrder',
            'group_by_field' => 'order_date',
            'chart_type' => 'bar',
            'filter_field' => 'order_date',
            'range_date_start' => Carbon::parse($request->from_date) ?? '',
            'range_date_end' => Carbon::parse($request->to_date),
            'group_by_period' => $request->report_type ?? 'day',
            'chart_height' => '200px',
            'aggregate_function'  => 'count',
            'date_format' => 'Y M d'
        ];
        $pos_order_reports = new LaravelChart($chart_options);

        $data = [
            'orders_count' => $ordersCount,
            'pos_orders_count' => $posOrdersCount,
            'orders' => $orders,
            'chart1' => $order_reports,
            'chart2' => $sales_report,
            'chart3' => $order_by_payment_method,
            'chart4' => $order_by_status,
            'chart5' => $pos_order_reports,
        ];
        return view('restaurants.dashboard', $data);
    }

    public function profileUpdatePage()
    {
        $restaurant = currentRestaurant();
        return view('restaurants.profile', compact('restaurant'));
    }

    public function resuscribe(Request $request)
    {
        currentRestaurant()->suscribe($request->plan_id);
    }

    public function profileUpdate(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'email' => ['required', 'unique:restaurants,email,' . currentRestaurant()->id],
            'phone' => ['required', 'unique:restaurants,phone,' . currentRestaurant()->id, 'digits:10'],
            'address' => ['required'],
            'photo' => ['nullable'],
        ]);
        $restaurant = Restaurant::find(currentRestaurant()->id);
        $restaurant->update($request->all());
        if ($request->has('photo') && $request->photo !== null) {
            $restaurant->clearMediaCollection();
            $restaurant->addMedia($request->photo)->toMediaCollection();
        }
        return redirect()->back()->with('success', 'Profile Updated Successfully');
    }

    public function changePassword(Request $request)
    {
        $restaurant = currentRestaurant();
        $request->validate([
            'old_password' => ['required'],
            'password' => ['required', 'confirmed'],
        ]);

        if (!Hash::check($request->old_password, $restaurant->owner->password)) {
            return redirect()->back()->with('error', 'Your Old Password is incorrect');
        }

        $restaurant->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->back()->with('success', 'Password Change Succesfully');
    }
}
