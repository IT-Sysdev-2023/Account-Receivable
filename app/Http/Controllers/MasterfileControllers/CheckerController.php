<?php

namespace App\Http\Controllers\MasterfileControllers;

use App\Events\NewCreated;
use App\Http\Controllers\Controller;
use App\Models\MasterfileModels\Checker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\WebpEncoder;

class CheckerController extends Controller
{

    public function index(Request $request)
    {
        return Inertia::render('Checkers', [
            // Fetch the users
            'checkers' => Checker::when($request->search, function ($query) use ($request) {
                $query
                    ->where('first_name', 'like', '%' . $request->search . '%')
                    ->orWhere('last_name', 'like', '%' . $request->search . '%');
            })->paginate(10)->withQueryString(),

            'searchTerm' => $request->search,
            'broadcastChannel' => 'checkers',
        ]);
    }

    public function addChecker(Request $request)
    {
        // sleep(1);

        //validate
        $fields = $request->validate([
            'photo' => ['nullable', 'image', 'max:5000'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
        ]);

        $fields['first_name'] = ucwords(strtolower($fields['first_name']));
        $fields['last_name'] = ucwords(strtolower($fields['last_name']));

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');

            // Create an Intervention Image instance
            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($file->getPathname());

            // Resize if larger than 300KB
            if ($file->getSize() > 300 * 1024) {
                $image->resize(800, 800, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Encode and store as WebP
            $filename = uniqid() . '.webp';
            $path = 'checkers/' . $filename;


            $encodedImage = $image->encode(new WebpEncoder(quality: 75));
            Storage::disk('public')->put($path, $encodedImage);

            // Save the public URL to the photo field
            $fields['photo'] = Storage::url($path);
        }

        //Add
        $fields['created_by'] =  $request->user()->name;
        Checker::create($fields);

        event(new NewCreated('checker'));
    }

    public function updateChecker(Request $request)
    {
        // sleep(1);

        $fields = $request->validate([
            'photo' => ['nullable', 'image', 'max:5000'],
            'first_name' => ['string', 'max:255'],
            'last_name' => ['string', 'max:255'],
        ]);

        $fields['first_name'] = ucwords(strtolower($fields['first_name']));
        $fields['last_name'] = ucwords(strtolower($fields['last_name']));

        $checker = Checker::findOrFail($request->id);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');

            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($file->getPathname());

            if ($file->getSize() > 300 * 1024) {
                $image->resize(800, 800, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $filename = uniqid() . '.webp';
            $path = 'checkers/' . $filename;

            $encodedImage = $image->encode(new WebpEncoder(quality: 75));
            Storage::disk('public')->put($path, $encodedImage);

            // Delete the old photo file if it exists
            if ($checker->photo) {
                $oldPath = str_replace('/storage/', '', $checker->photo);
                Storage::disk('public')->delete($oldPath);
            }
            // Save the new photo URL
            $fields['photo'] = Storage::url($path);
        } else {
            // If no new photo uploaded, retain the old one
            $fields['photo'] = $checker->photo;
        }

        // Update the record
        $fields['created_by'] =  $request->user()->name;
        $checker->update($fields);

        event(new NewCreated('checker'));
    }

    public function destroy($id)
    {
        $checker = Checker::findOrFail($id);

        // Delete photo if it exists
        if ($checker->photo) {
            $path = str_replace('/storage/', '', $checker->photo); // Convert URL to storage path
            Storage::disk('public')->delete($path);
        }
        $checker->delete();

        event(new NewCreated('checker'));
    }
}
