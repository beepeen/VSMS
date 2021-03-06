<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Bike;
use App\Part;
use DB;
use Carbon\Carbon;

class BillController extends Controller
{
    //
     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
//        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function bike()
    {
        if(Auth::user()){
            $b=DB::select('SELECT *  FROM `bikes` ');
//            $q=DB::table('product')->get();
        return view('bill.bikebill',array('user'=>Auth::user(),'data'=>$b));
             }
        else{
            return redirect()->back();
        }
    }
     public function vat()
    {
        if(Auth::user()){
            $b=DB::select('SELECT *  FROM `bikes` ');
//            $q=DB::table('product')->get();
        return view('bill.vatbill',array('user'=>Auth::user(),'data'=>$b));
             }
        else{
            return redirect()->back();
        }
    }
    
    //demo
     public function demo()
    {
        if(Auth::user()){
           
//            $q=DB::table('product')->get();
        return view('bill.printbill',array('user'=>Auth::user()));
             }
        else{
            return redirect()->back();
        }
    }
    
    //demo
    
      public function parts()
    {
        if(Auth::user()){
            $q=DB::table('parts')->get();
            $b=DB::select('SELECT parts_name,COUNT(parts_name) as units FROM `parts` GROUP by parts_name');
        return view('bill.partsbill',array('user'=>Auth::user(),'data'=>$q));
             }
        else{
            return redirect()->back();
        }
    }
     public function partssave(request $request){
        $post=$request->all();
        $today=Carbon::now();
        $data=array(
       'ordername'=>$post['ordername'],
        'cusname'=>$post['customername'],
        'cusaddress'=>$post['customeraddress'],
        'cusphone'=>$post['customerphone'],
        );
       
        $j=DB::table('orders')->insertGetId($data);
        if($j > 0){
            for($i=0;$i < count($post['product_id']); $i++){
                $c=DB::table('services')->where('bike_model',$post['bike_model'][$i]);
               $datadetail=array(
                'order_id'=>$j,
                'bike_id'=>$post['product_id'][$i],
                'bike_model'=>$post['bike_model'][$i],
                'chasis'=>$post['chasis'][$i],
                'engine'=>$post['engine'][$i],
                'reg_no'=>$post['reg_no'][$i],
                'price'=>$post['price'][$i]-$post['dis'][$i],
                'cusname'=>$post['customername'],
        'cusaddres'=>$post['customeraddress'],
        'cusphone'=>$post['customerphone'],
                   'created_at'=>$today
                   
               );
 
                DB::table('customers')->insert($datadetail);
                DB::table('bikesales')->insert($datadetail);
                            
    
            }
            return redirect()->back();
        }
    }
     public function partsbillsave(Request $request){
        $post=$request->all();
//         $up=array('status'=>1);
//        DB::table('parts')->find($post['product_id'])->update($up);
        $data=array(
       'ordername'=>$post['ordername'],
        'cusname'=>$post['customername'],
        'cusaddres'=>$post['customeraddress'],
        'cusphone'=>$post['customerphone'],
        );
           $cudata=array(
       
        'cusname'=>$post['customername'],
        'cusaddres'=>$post['customeraddress'],
        'cusphone'=>$post['customerphone']
        );
         $ci=DB::table('customers')->insertGetId($cudata);
        $j=DB::table('ordersparts')->insertGetId($data);
        if($j > 0){
            for($i=0;$i < count($post['product_id']); $i++){
               
               $datadetail=array(
                'order_id'=>$j,
                'parts_id'=>$post['product_id'][$i],
                
               
                'bike_model'=>11,
                'parts_name'=>$post['parts_name'][$i],
                'parts_no'=>$post['reg_no'][$i],
                'price'=>$post['price'][$i]+((($post['dis'][$i])*$post['price'][$i])/100),
                'cusname'=>$post['customername'],
        'cusaddres'=>$post['customeraddress'],
        'cusphone'=>$post['customerphone']
                   
               );
                DB::table('partssell')->insert($datadetail);
                //ledger
                $ledgerdata=array(
                'credit'=>$post['price'][$i]+((($post['dis'][$i])*$post['price'][$i])/100),
                'purpose'=>"Bikes parts",
                'debit'=>0,
                'cid'=>$ci
            );
            
            DB::table('ledgers')->insert($ledgerdata);
            
            
            //end of ledger
                
            }
            //ledger
                $ledgerdata=array(
                'credit'=>0,
                'purpose'=>"Bikes parts",
                'debit'=>$post['debit'],
                'cid'=>$ci
            );
            
            DB::table('ledgers')->insert($ledgerdata);
            
            
            //end of ledger
            return redirect()->back();
        }
    }
    public function bikesave(request $request){
        $post=$request->all();
       $up=array('status'=>1);
        $bkup=Bike::find($post['product_id'])->update($up);
// $ct=DB::select('select timeinterval from services where bike_model="'.$ab.'"');
        $data=array(
       'ordername'=>$post['ordername'],
        'cusname'=>$post['customername'],
        'cusaddress'=>$post['customeraddress'],
        'cusphone'=>$post['customerphone'],
        );
        $j=DB::table('orders')->insertGetId($data);
        if($j > 0){
            
//                $c=DB::table('services')->where('bike_model',$post['bike_model']);
               $datadetail=array(
                'order_id'=>$j,
                'bike_id'=>$post['product_id'],
                'bike_model'=>$post['bike_model'],
                'chasis'=>$post['chasis'],
                'engine'=>$post['engine'],
                'reg_no'=>$post['reg_no'],
                'price'=>$post['price']-$post['dis'],
                'cusname'=>$post['customername'],
        'cusaddres'=>$post['customeraddress'],
        'cusphone'=>$post['customerphone']
                   
               );
          
//                DB::table('customer')->insert($datadetail);
                DB::table('bikesales')->insert($datadetail);
            $ci=DB::table('customers')->insertGetId($datadetail);
               $ab=$post['bike_model'];
            
            // prepare ledger
            $ledgerdata=array(
                'credit'=>$post['amount'],
                'purpose'=>"Bikes",
                'debit'=>$post['debit'],
                'cid'=>$ci
            );
            
            DB::table('ledgers')->insert($ledgerdata);
            
            
            //end of ledger
 $cd=DB::table('services')->where('bike_model',$ab)->get();
       foreach($cd as $cds){
       $cid=$cds->name;
       $csv=$cds->type;
       $ct=$cds->timeinterval; 
//           $cdate=$post['tdate'];
//        $cst=$cdate($cdate,strtotime($ct));
//            
           $dts=Carbon::now();
           $srvt=$dts->addDays($ct);
           
                if($ci > 0){
                $servicedetail=array(
                'cid'=>$ci,
                'bike_id'=>$post['product_id'],
                'bike_model'=>$post['bike_model'],
                'serv_name'=>$cid,
                'serv_type'=>$csv,
                
                'servicetime'=>$ct,
               'servetime'=>$srvt
                
                    
                );
                }
                DB::table('customerservices')->insert($servicedetail); 
       }
        
            
            return redirect()->back();
        }
        
    }
    
   public function testsave(request $request){
        $post=$request->all();
       $up=array('status'=>1);
        $bkup=Bike::find($post['product_id'])->update($up);
// $ct=DB::select('select timeinterval from services where bike_model="'.$ab.'"');
        $data=array(
       'ordername'=>$post['ordername'],
        'cusname'=>$post['customername'],
        'cusaddress'=>$post['customeraddress'],
        'cusphone'=>$post['customerphone'],
        );
        $j=DB::table('orders')->insertGetId($data);
        if($j > 0){
            
//                $c=DB::table('services')->where('bike_model',$post['bike_model']);
               $datadetail=array(
                'order_id'=>$j,
                'bike_id'=>$post['product_id'],
                'bike_model'=>$post['bike_model'],
                'chasis'=>$post['chasis'],
                'engine'=>$post['engine'],
                'reg_no'=>$post['reg_no'],
                'price'=>$post['price']-$post['dis'],
                'cusname'=>$post['customername'],
        'cusaddres'=>$post['customeraddress'],
        'cusphone'=>$post['customerphone']
                   
               );
          
//                DB::table('customer')->insert($datadetail);
                DB::table('bikesales')->insert($datadetail);
            $ci=DB::table('customers')->insertGetId($datadetail);
               $ab=$post['bike_model'];
            
            // prepare ledger
            $ledgerdata=array(
                'credit'=>$post['amount'],
                'purpose'=>"Bikes",
                'debit'=>$post['debit'],
                'cid'=>$ci
            );
            
            DB::table('ledgers')->insert($ledgerdata);
            
            
            //end of ledger
 $cd=DB::table('services')->where('bike_model',$ab)->get();
       foreach($cd as $cds){
       $cid=$cds->name;
       $csv=$cds->type;
       $ct=$cds->timeinterval; 
//           $cdate=$post['tdate'];
//        $cst=$cdate($cdate,strtotime($ct));
//            
           $dts=Carbon::now();
           $srvt=$dts->addDays($ct);
           
                if($ci > 0){
                $servicedetail=array(
                'cid'=>$ci,
                'bike_id'=>$post['product_id'],
                'bike_model'=>$post['bike_model'],
                'serv_name'=>$cid,
                'serv_type'=>$csv,
                
                'servicetime'=>$ct,
               'servetime'=>$srvt
                
                    
                );
                }
                DB::table('customerservices')->insert($servicedetail); 
       }
        
            
            return redirect()->back();
        }
        
    }
   public function test()
    {
        if(Auth::user()){
            $b=DB::select('SELECT *  FROM `bikes` where status=0 ');
//            $q=DB::table('product')->get();
        return view('bill.testbill',array('user'=>Auth::user(),'data'=>$b));
             }
        else{
            return redirect()->back();
        }
    }

}
