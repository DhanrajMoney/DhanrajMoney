<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Frontend;
use App\Models\GeneralSetting;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Image;
use App\Constants\Status;

class GeneralSettingController extends Controller
{
    public function index()
    {
        $pageTitle = 'General Setting';
        $timezones = json_decode(file_get_contents(resource_path('views/admin/partials/timezone.json')));
        return view('admin.setting.general', compact('pageTitle','timezones'));
    }

    public function update(Request $request)
    {   
        $request->validate([
            'site_name' => 'required|string|max:40',
            'cur_text' => 'required|string|max:40',
            'cur_sym' => 'required|string|max:40',
            'base_color' => 'nullable', 'regex:/^[a-f0-9]{6}$/i',
            'secondary_color' => 'nullable', 'regex:/^[a-f0-9]{6}$/i',
            'timezone' => 'required',
        ]);

        $general = GeneralSetting::first();
        $general->site_name = $request->site_name;
        $general->cur_text = $request->cur_text;
        $general->cur_sym = $request->cur_sym;
        $general->base_color = $request->base_color;
        $general->secondary_color = $request->secondary_color;
        $general->save();

        $timezoneFile = config_path('timezone.php');
        $content = '<?php $timezone = '.$request->timezone.' ?>';
        file_put_contents($timezoneFile, $content);
        $notify[] = ['success', 'General setting updated successfully'];
        return back()->withNotify($notify);
    }

    public function systemConfiguration(){
        $temPaths = array_filter(glob('core/resources/views/templates/*'), 'is_dir');
        $firstTem = explode('/', array_shift($temPaths));
        $firstTemName = end($firstTem);
        $pageTitle = 'System Configuration';
        return view('admin.setting.configuration', compact('pageTitle', 'firstTemName'));
    }


    public function systemConfigurationSubmit(Request $request)
    {   
        $general = gs();
        $general->ev = $request->ev ? 1 : 0;
        $general->en = $request->en ? 1 : 0;
        $general->sv = $request->sv ? 1 : 0;
        $general->sn = $request->sn ? 1 : 0;

        $general->enable_language = $request->enable_language ? Status::ENABLE : Status::DISABLE;

        $general->force_ssl = $request->force_ssl ? 1 : 0;
        $general->secure_password = $request->secure_password ? Status::ENABLE : Status::DISABLE;
        $general->registration = $request->registration ? Status::ENABLE : Status::DISABLE;
        $general->agree = $request->agree ? Status::ENABLE : Status::DISABLE;

        $general->dark_template = $request->dark_template ? 1 : 0;
        $general->deposit_commission = $request->deposit_commission ? 1 : 0;

        $general->save();

        $notify[] = ['success', 'System configuration updated successfully'];
        return back()->withNotify($notify);
    }


    public function logoIcon()
    {
        $pageTitle = 'Logo & Favicon';
        return view('admin.setting.logo_icon', compact('pageTitle'));
    }

    public function logoIconUpdate(Request $request)
    {   
        $request->validate([
            'logo' => ['image',new FileTypeValidate(['jpg','jpeg','png'])],
            'dark_logo' => ['image',new FileTypeValidate(['jpg','jpeg','png'])],
            'favicon' => ['image',new FileTypeValidate(['png'])],
        ]);
        if ($request->hasFile('logo')) {
            try {
                $path = getFilePath('logoIcon');
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                Image::make($request->logo)->save($path . '/logo.png');
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload the logo'];
                return back()->withNotify($notify);
            }
        }
        if ($request->hasFile('dark_logo')) { 
            try {
                $path = getFilePath('logoIcon'); 
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                Image::make($request->dark_logo)->save($path . '/dark_logo.png');
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload the dark logo'];
                return back()->withNotify($notify);
            }
        }
        if ($request->hasFile('favicon')) {
            try {
                $path = getFilePath('logoIcon');
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                $size = explode('x', getFileSize('favicon'));
                Image::make($request->favicon)->resize($size[0], $size[1])->save($path . '/favicon.png');
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload the favicon'];
                return back()->withNotify($notify);
            }
        }
        $notify[] = ['success', 'Logo & favicon updated successfully'];
        return back()->withNotify($notify);
    }

    public function customCss(){
        $pageTitle = 'Custom CSS';
        $file = activeTemplate(true).'css/custom.css';
        $file_content = @file_get_contents($file);
        return view('admin.setting.custom_css',compact('pageTitle','file_content'));
    }


    public function customCssSubmit(Request $request){
        $file = activeTemplate(true).'css/custom.css';
        if (!file_exists($file)) {
            fopen($file, "w");
        }
        file_put_contents($file,$request->css);
        $notify[] = ['success','CSS updated successfully'];
        return back()->withNotify($notify);
    }

    public function maintenanceMode()
    {
        $pageTitle = 'Maintenance Mode';
        $maintenance = Frontend::where('data_keys','maintenance.data')->firstOrFail();
        return view('admin.setting.maintenance',compact('pageTitle','maintenance'));
    }

    public function maintenanceModeSubmit(Request $request)
    {
        $request->validate([
            'description'=>'required'
        ]);
        $general = gs();
        $general->maintenance_mode = $request->status ? Status::ENABLE : Status::DISABLE;
        $general->save();

        $maintenance = Frontend::where('data_keys','maintenance.data')->firstOrFail();
        $maintenance->data_values = [
            'description' => $request->description,
        ];
        $maintenance->save();

        $notify[] = ['success','Maintenance mode updated successfully'];
        return back()->withNotify($notify);
    }

    public function cookie(){
        $pageTitle = 'GDPR Cookie';
        $cookie = Frontend::where('data_keys','cookie.data')->firstOrFail();
        return view('admin.setting.cookie',compact('pageTitle','cookie'));
    }

    public function cookieSubmit(Request $request){
        $request->validate([
            'short_desc'=>'required|string|max:255',
            'description'=>'required',
        ]);
        $cookie = Frontend::where('data_keys','cookie.data')->firstOrFail();
        $cookie->data_values = [
            'short_desc' => $request->short_desc,
            'description' => $request->description,
            'status' => $request->status ? 1 : 0,
        ];
        $cookie->save();
        $notify[] = ['success','Cookie policy updated successfully'];
        return back()->withNotify($notify);
    }
}
