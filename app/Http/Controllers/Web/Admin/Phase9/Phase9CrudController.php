<?php

namespace App\Http\Controllers\Web\Admin\Phase9;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Student;
use App\Models\Academic\Subject;
use App\Models\AI\AiProvider;
use App\Models\Canteen\CanteenCategory;
use App\Models\Canteen\CanteenMenuItem;
use App\Models\LessonPlan\LessonPlan;
use App\Models\LiveClass\LiveClassSession;
use App\Models\Religious\HafalanProgress;
use App\Models\Religious\HafalanTarget;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Phase9CrudController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }

    /* ============== LESSON PLAN ============== */

    public function lessonPlans(): View
    {
        return view('school-admin.lesson-plan.index', [
            'plans' => LessonPlan::where('school_id', $this->schoolId())
                ->with(['subject', 'classSection.classRoom', 'teacher:id,name'])
                ->orderByDesc('created_at')->paginate(20),
            'subjects'      => Subject::where('school_id', $this->schoolId())->get(),
            'classSections' => ClassSection::where('school_id', $this->schoolId())->with(['classRoom', 'section'])->get(),
            'teachers'      => User::where('school_id', $this->schoolId())
                ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))->get(['id', 'name']),
        ]);
    }

    public function storeLessonPlan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'                => 'required|string|max:255',
            'subject_id'           => 'required|exists:subjects,id',
            'class_section_id'     => 'required|exists:class_sections,id',
            'teacher_id'           => 'required|exists:users,id',
            'lesson_date'          => 'nullable|date',
            'duration_minutes'     => 'nullable|integer|min:15|max:300',
            'learning_objectives'  => 'nullable|string',
            'material_summary'     => 'nullable|string',
        ]);
        $data['school_id'] = $this->schoolId();
        $data['status']    = 'draft';
        LessonPlan::create($data);
        return back()->with('success', 'Lesson plan ditambahkan.');
    }

    public function deleteLessonPlan(LessonPlan $plan): RedirectResponse
    {
        $this->authorizeOwn($plan);
        $plan->delete();
        return back()->with('success', 'Lesson plan dihapus.');
    }

    /* ============== LIVE CLASS ============== */

    public function liveClassSessions(): View
    {
        return view('school-admin.live-class.sessions', [
            'sessions' => LiveClassSession::where('school_id', $this->schoolId())
                ->with(['classSection.classRoom', 'subject', 'teacher:id,name'])
                ->orderByDesc('scheduled_start')->paginate(20),
            'subjects'      => Subject::where('school_id', $this->schoolId())->get(),
            'classSections' => ClassSection::where('school_id', $this->schoolId())->with(['classRoom', 'section'])->get(),
            'teachers'      => User::where('school_id', $this->schoolId())
                ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))->get(['id', 'name']),
        ]);
    }

    public function storeLiveClassSession(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'topic'            => 'required|string|max:255',
            'class_section_id' => 'required|exists:class_sections,id',
            'subject_id'       => 'required|exists:subjects,id',
            'teacher_id'       => 'required|exists:users,id',
            'join_url'         => 'required|url|max:500',
            'meeting_id'       => 'nullable|string|max:100',
            'passcode'         => 'nullable|string|max:50',
            'scheduled_start'  => 'required|date',
            'duration_minutes' => 'required|integer|min:15|max:480',
        ]);
        $data['school_id'] = $this->schoolId();
        $data['status'] = 'scheduled';
        LiveClassSession::create($data);
        return back()->with('success', 'Sesi live class dijadwalkan.');
    }

    public function deleteLiveClassSession(LiveClassSession $session): RedirectResponse
    {
        $this->authorizeOwn($session);
        $session->delete();
        return back()->with('success', 'Sesi dihapus.');
    }

    /* ============== AI PROVIDERS (per school) ============== */

    public function aiProviders(): View
    {
        return view('school-admin.ai.providers', [
            'providers' => AiProvider::where('school_id', $this->schoolId())->orderBy('priority')->orderBy('name')->get(),
            'formats'   => ['openai_compatible', 'anthropic', 'gemini', 'image_generic'],
        ]);
    }

    public function storeAiProvider(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:200',
            'slug'       => 'required|alpha_dash|max:100',
            'api_format' => 'required|string|max:40',
            'base_url'   => 'nullable|url|max:500',
            'api_key'    => 'nullable|string|max:500',
            'priority'   => 'nullable|integer|min:0|max:100',
        ]);

        $provider = new AiProvider();
        $provider->fill([
            'school_id'  => $this->schoolId(),
            'name'       => $data['name'],
            'slug'       => $data['slug'],
            'api_format' => $data['api_format'],
            'base_url'   => $data['base_url'] ?? null,
            'is_sandbox' => false,
            'is_active'  => true,
            'priority'   => $data['priority'] ?? 0,
        ]);
        $provider->api_key = $data['api_key'] ?? null;
        $provider->save();

        return back()->with('success', 'AI provider ditambahkan.');
    }

    public function deleteAiProvider(AiProvider $provider): RedirectResponse
    {
        $this->authorizeOwn($provider);
        $provider->delete();
        return back()->with('success', 'AI provider dihapus.');
    }

    /* ============== KANTIN ============== */

    public function canteenCategories(): View
    {
        return view('school-admin.canteen.categories', [
            'categories' => CanteenCategory::where('school_id', $this->schoolId())->orderBy('name')->get(),
        ]);
    }

    public function storeCanteenCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'icon'        => 'nullable|string|max:50',
            'healthy_tag' => 'nullable|boolean',
        ]);
        $data['school_id']   = $this->schoolId();
        $data['healthy_tag'] = (bool) ($data['healthy_tag'] ?? false);
        CanteenCategory::create($data);
        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function deleteCanteenCategory(CanteenCategory $category): RedirectResponse
    {
        $this->authorizeOwn($category);
        $category->delete();
        return back()->with('success', 'Kategori dihapus.');
    }

    public function canteenMenu(): View
    {
        return view('school-admin.canteen.menu', [
            'items' => CanteenMenuItem::where('school_id', $this->schoolId())->with('category')->orderBy('name')->get(),
            'categories' => CanteenCategory::where('school_id', $this->schoolId())->orderBy('name')->get(),
        ]);
    }

    public function storeCanteenMenuItem(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:200',
            'canteen_category_id'   => 'required|exists:canteen_categories,id',
            'price_rupiah'          => 'required|numeric|min:0',
            'description'           => 'nullable|string|max:500',
            'is_available'          => 'nullable|boolean',
        ]);
        CanteenMenuItem::create([
            'school_id'           => $this->schoolId(),
            'canteen_category_id' => $data['canteen_category_id'],
            'name'                => $data['name'],
            'price'               => (int)($data['price_rupiah'] * 100),
            'description'         => $data['description'] ?? null,
            'is_available'        => true,
        ]);
        return back()->with('success', 'Menu ditambahkan.');
    }

    public function deleteCanteenMenuItem(CanteenMenuItem $item): RedirectResponse
    {
        $this->authorizeOwn($item);
        $item->delete();
        return back()->with('success', 'Menu dihapus.');
    }

    /* ============== RELIGIOUS / PESANTREN ============== */

    public function hafalanTargets(): View
    {
        return view('school-admin.religious.targets', [
            'targets' => HafalanTarget::where('school_id', $this->schoolId())->orderBy('name')->get(),
        ]);
    }

    public function storeHafalanTarget(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:200',
            'class_section_id' => 'nullable|exists:class_sections,id',
            'start_date'       => 'required|date',
            'deadline'         => 'required|date|after_or_equal:start_date',
            'target_ranges'    => 'nullable|string',
        ]);
        HafalanTarget::create([
            'school_id'        => $this->schoolId(),
            'name'             => $data['name'],
            'class_section_id' => $data['class_section_id'] ?? null,
            'start_date'       => $data['start_date'],
            'deadline'         => $data['deadline'],
            'target_ranges'    => $data['target_ranges'] ? [['ranges' => $data['target_ranges']]] : [],
        ]);
        return back()->with('success', 'Target hafalan ditambahkan.');
    }

    public function deleteHafalanTarget(HafalanTarget $target): RedirectResponse
    {
        $this->authorizeOwn($target);
        $target->delete();
        return back()->with('success', 'Target dihapus.');
    }

    public function hafalanProgress(): View
    {
        return view('school-admin.religious.progress', [
            'progresses' => HafalanProgress::where('school_id', $this->schoolId())
                ->with(['student.user:id,name', 'target', 'verifier:id,name'])
                ->orderByDesc('memorized_at')->paginate(25),
            'targets'  => HafalanTarget::where('school_id', $this->schoolId())->get(),
            'students' => Student::where('school_id', $this->schoolId())->with('user:id,name')->get(),
        ]);
    }

    public function storeHafalanProgress(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'        => 'required|exists:students,id',
            'hafalan_target_id' => 'required|exists:hafalan_targets,id',
            'surah'             => 'required|string|max:100',
            'ayah_start'        => 'required|integer|min:1',
            'ayah_end'          => 'required|integer|min:1|gte:ayah_start',
            'memorized_at'      => 'required|date',
            'quality'           => 'required|in:excellent,good,fair,needs_review',
            'note'              => 'nullable|string|max:500',
        ]);
        $data['school_id']   = $this->schoolId();
        $data['verified_by'] = auth()->id();
        HafalanProgress::create($data);
        return back()->with('success', 'Progress hafalan tersimpan.');
    }
}
