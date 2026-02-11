<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dashboard extends Model
{
    //objek untuk laporan dashboard
    protected $fillable=[
      'dash_company_name', 
      'dash_active_year',
      'dash_profit',
      'dash_cost',
      'dash_revenue',
      'dash_cust',
      'dash_profit_month',
      'dash_cost_month',
      'dash_revenue_month',
      'dash_cust_month',
      'dash_profit_last',
      'dash_cost_last',
      'dash_revenue_last',
      'dash_cust_last',
      'dash_sales_qty',
      'dash_sales_cart_monthly',
      'dash_chart_prodvaltopten',
      'dash_chart_prodpricetopten',
      'dash_pending_sales',
      'dash_pending_qty',
      'dash_spot_total',
      'dash_spot_avail',
      'dash_sales_value',
      'dash_sales_all',
      'dash_total_store',
      'dash_not_active_store',
      'dash_sales_chart_yearly',
      
      // 'dash_sales_value_margin',
      // 'dash_sales_cart_value'
      ];
}
