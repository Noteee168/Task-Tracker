<?php

use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

test('user can only see their own tasks on the dashboard', function () {
    // Tugas milik user yang login
    $myTask = Task::factory()->create([
        'user_id' => $this->user->id,
        'judul' => 'Tugas Pribadi Saya',
    ]);

    // Tugas milik user lain
    $otherTask = Task::factory()->create([
        'user_id' => $this->otherUser->id,
        'judul' => 'Tugas Rahasia Pengguna Lain',
    ]);

    $response = $this->actingAs($this->user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Tugas Pribadi Saya');
    $response->assertDontSee('Tugas Rahasia Pengguna Lain');
});

test('user can create a new task and it is assigned to the authenticated user', function () {
    $response = $this->actingAs($this->user)->post(route('tasks.store'), [
        'judul' => 'Task Baru User A',
        'deskripsi' => 'Deskripsi task',
        'nama_klien' => 'Klien A',
        'prioritas' => 'tinggi',
        'tanggal_deadline' => now()->addDays(5)->toDateString(),
        'status' => 'belum_dikerjakan',
    ]);

    $response->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('tasks', [
        'judul' => 'Task Baru User A',
        'user_id' => $this->user->id,
    ]);
});

test('user cannot edit another users task', function () {
    $otherTask = Task::factory()->create([
        'user_id' => $this->otherUser->id,
    ]);

    $response = $this->actingAs($this->user)->get(route('tasks.edit', $otherTask));

    $response->assertForbidden();
});

test('user cannot update another users task', function () {
    $otherTask = Task::factory()->create([
        'user_id' => $this->otherUser->id,
        'judul' => 'Judul Awal',
    ]);

    $response = $this->actingAs($this->user)->put(route('tasks.update', $otherTask), [
        'judul' => 'Mencoba Bajak Tugas',
        'prioritas' => 'tinggi',
        'tanggal_deadline' => now()->addDays(3)->toDateString(),
        'status' => 'sedang_dikerjakan',
    ]);

    $response->assertForbidden();
    expect($otherTask->fresh()->judul)->toBe('Judul Awal');
});

test('user cannot delete another users task', function () {
    $otherTask = Task::factory()->create([
        'user_id' => $this->otherUser->id,
    ]);

    $response = $this->actingAs($this->user)->delete(route('tasks.destroy', $otherTask));

    $response->assertForbidden();
    $this->assertDatabaseHas('tasks', ['id' => $otherTask->id]);
});

test('user cannot quick update status of another users task', function () {
    $otherTask = Task::factory()->create([
        'user_id' => $this->otherUser->id,
        'status' => 'belum_dikerjakan',
    ]);

    $response = $this->actingAs($this->user)->patch(route('tasks.update-status', $otherTask), [
        'status' => 'sedang_dikerjakan',
    ]);

    $response->assertForbidden();
    expect($otherTask->fresh()->status)->toBe('belum_dikerjakan');
});

test('user can update status of their own task', function () {
    $myTask = Task::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'belum_dikerjakan',
    ]);

    $response = $this->actingAs($this->user)->patch(route('tasks.update-status', $myTask), [
        'status' => 'sedang_dikerjakan',
    ]);

    $response->assertRedirect();
    expect($myTask->fresh()->status)->toBe('sedang_dikerjakan');
});

test('user cannot unsubmit a completed task via quick status update', function () {
    $completedTask = Task::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'selesai',
    ]);

    $response = $this->actingAs($this->user)->patch(route('tasks.update-status', $completedTask), [
        'status' => 'sedang_dikerjakan',
    ]);

    $response->assertSessionHas('error');
    expect($completedTask->fresh()->status)->toBe('selesai');
});

test('guest cannot access calendar view and is redirected to login', function () {
    $response = $this->get(route('calendar'));

    $response->assertRedirect(route('login'));
});

test('authenticated user can view calendar page and only sees their own tasks', function () {
    $myTask = Task::factory()->create([
        'user_id' => $this->user->id,
        'judul' => 'Tugas Spesifik Kalender Saya',
        'tanggal_deadline' => now()->addDays(2)->toDateString(),
    ]);

    $otherTask = Task::factory()->create([
        'user_id' => $this->otherUser->id,
        'judul' => 'Tugas Orang Lain di Kalender',
        'tanggal_deadline' => now()->addDays(2)->toDateString(),
    ]);

    $response = $this->actingAs($this->user)->get(route('calendar'));

    $response->assertOk();
    $response->assertSee('Kalender Deadline Tugas');
    $response->assertSee('Tugas Spesifik Kalender Saya');
    $response->assertDontSee('Tugas Orang Lain di Kalender');
});

test('dashboard renders kanban board view elements', function () {
    $task = Task::factory()->create([
        'user_id' => $this->user->id,
        'judul' => 'Task Kanban Drag Drop',
        'status' => 'sedang_dikerjakan',
    ]);

    $response = $this->actingAs($this->user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Kanban Board');
    $response->assertSee('kanban-col-sedang_dikerjakan', false);
    $response->assertSee('Task Kanban Drag Drop');
});

test('user can update task status via json api for kanban drag and drop', function () {
    $task = Task::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'belum_dikerjakan',
    ]);

    $response = $this->actingAs($this->user)->patchJson(route('tasks.update-status', $task), [
        'status' => 'sedang_dikerjakan',
    ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'task' => [
            'id' => $task->id,
            'status' => 'sedang_dikerjakan',
        ],
    ]);

    expect($task->fresh()->status)->toBe('sedang_dikerjakan');
});

test('user cannot unsubmit completed task via json api in kanban drag and drop', function () {
    $task = Task::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'selesai',
    ]);

    $response = $this->actingAs($this->user)->patchJson(route('tasks.update-status', $task), [
        'status' => 'sedang_dikerjakan',
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
    ]);

    expect($task->fresh()->status)->toBe('selesai');
});

test('deleting a task soft deletes it instead of wiping it immediately from the database', function () {
    $task = Task::factory()->create([
        'user_id' => $this->user->id,
        'judul' => 'Tugas Yang Mau Dihapus',
    ]);

    $response = $this->actingAs($this->user)->delete(route('tasks.destroy', $task));

    $response->assertRedirect();
    $this->assertSoftDeleted('tasks', [
        'id' => $task->id,
        'judul' => 'Tugas Yang Mau Dihapus',
    ]);

    // Pastikan tidak lagi muncul di dashboard aktif
    session()->forget('success');
    $dashboard = $this->actingAs($this->user)->get(route('dashboard'));
    $dashboard->assertDontSee('Tugas Yang Mau Dihapus');
});

test('user can view trash page and only sees their own soft deleted tasks', function () {
    $myTrashedTask = Task::factory()->create([
        'user_id' => $this->user->id,
        'judul' => 'Tugas Sampah Saya',
        'deleted_at' => now(),
    ]);

    $otherTrashedTask = Task::factory()->create([
        'user_id' => $this->otherUser->id,
        'judul' => 'Tugas Sampah Pengguna Lain',
        'deleted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->get(route('tasks.trash'));

    $response->assertOk();
    $response->assertSee('Tong Sampah');
    $response->assertSee('Tugas Sampah Saya');
    $response->assertDontSee('Tugas Sampah Pengguna Lain');
});

test('user can restore a soft deleted task back to active dashboard', function () {
    $trashedTask = Task::factory()->create([
        'user_id' => $this->user->id,
        'judul' => 'Tugas Yang Akan Dipulihkan',
        'deleted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->patch(route('tasks.restore', $trashedTask->id));

    $response->assertRedirect();
    $this->assertNotSoftDeleted('tasks', [
        'id' => $trashedTask->id,
    ]);

    // Muncul kembali di dashboard
    $dashboard = $this->actingAs($this->user)->get(route('dashboard'));
    $dashboard->assertSee('Tugas Yang Akan Dipulihkan');
});

test('user can permanently force delete a soft deleted task', function () {
    $trashedTask = Task::factory()->create([
        'user_id' => $this->user->id,
        'judul' => 'Tugas Hapus Permanen',
        'deleted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->delete(route('tasks.force-delete', $trashedTask->id));

    $response->assertRedirect();
    $this->assertDatabaseMissing('tasks', [
        'id' => $trashedTask->id,
    ]);
});

test('user can empty their entire trash at once', function () {
    $task1 = Task::factory()->create([
        'user_id' => $this->user->id,
        'deleted_at' => now(),
    ]);
    $task2 = Task::factory()->create([
        'user_id' => $this->user->id,
        'deleted_at' => now(),
    ]);
    $otherTrashed = Task::factory()->create([
        'user_id' => $this->otherUser->id,
        'deleted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->post(route('tasks.empty-trash'));

    $response->assertRedirect();
    $this->assertDatabaseMissing('tasks', ['id' => $task1->id]);
    $this->assertDatabaseMissing('tasks', ['id' => $task2->id]);
    $this->assertDatabaseHas('tasks', ['id' => $otherTrashed->id]);
});

test('user cannot restore or force delete another users trashed task', function () {
    $otherTrashedTask = Task::factory()->create([
        'user_id' => $this->otherUser->id,
        'judul' => 'Sampah Orang Lain',
        'deleted_at' => now(),
    ]);

    // Coba restore
    $restoreResponse = $this->actingAs($this->user)->patch(route('tasks.restore', $otherTrashedTask->id));
    $restoreResponse->assertForbidden();

    // Coba force delete
    $forceDeleteResponse = $this->actingAs($this->user)->delete(route('tasks.force-delete', $otherTrashedTask->id));
    $forceDeleteResponse->assertForbidden();

    // Tetap ada di database dan tetap soft-deleted
    $this->assertSoftDeleted('tasks', ['id' => $otherTrashedTask->id]);
});
