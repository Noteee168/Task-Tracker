<?php

use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->myTask = Task::factory()->create([
        'user_id' => $this->user->id,
        'judul' => 'Tugas Utama Pengguna A',
    ]);

    $this->otherTask = Task::factory()->create([
        'user_id' => $this->otherUser->id,
        'judul' => 'Tugas Pengguna Lain',
    ]);
});

test('user can view subtasks of their own task via api', function () {
    Subtask::factory()->create([
        'task_id' => $this->myTask->id,
        'judul' => 'Langkah Pertama Saya',
        'is_completed' => false,
    ]);

    $response = $this->actingAs($this->user)->getJson(route('subtasks.index', $this->myTask));

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'task_id' => $this->myTask->id,
        'progress' => [
            'total' => 1,
            'completed' => 0,
            'percentage' => 0,
        ],
    ]);
});

test('user cannot view subtasks of another users task', function () {
    $response = $this->actingAs($this->user)->getJson(route('subtasks.index', $this->otherTask));

    $response->assertForbidden();
});

test('user can add a subtask to their task', function () {
    $response = $this->actingAs($this->user)->postJson(route('subtasks.store', $this->myTask), [
        'judul' => 'Slicing UI Header',
    ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'subtask' => [
            'task_id' => $this->myTask->id,
            'judul' => 'Slicing UI Header',
            'is_completed' => false,
        ],
        'progress' => [
            'total' => 1,
            'completed' => 0,
            'percentage' => 0,
        ],
    ]);

    $this->assertDatabaseHas('subtasks', [
        'task_id' => $this->myTask->id,
        'judul' => 'Slicing UI Header',
        'is_completed' => false,
    ]);
});

test('subtask requires a valid title', function () {
    $response = $this->actingAs($this->user)->postJson(route('subtasks.store', $this->myTask), [
        'judul' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['judul']);
});

test('user cannot add a subtask to another users task', function () {
    $response = $this->actingAs($this->user)->postJson(route('subtasks.store', $this->otherTask), [
        'judul' => 'Coba bajak subtask',
    ]);

    $response->assertForbidden();
    $this->assertDatabaseMissing('subtasks', [
        'judul' => 'Coba bajak subtask',
    ]);
});

test('user can toggle subtask completion status', function () {
    $subtask = Subtask::factory()->create([
        'task_id' => $this->myTask->id,
        'judul' => 'Testing Toggle',
        'is_completed' => false,
    ]);

    // Centang jadi selesai
    $response = $this->actingAs($this->user)->patchJson(route('subtasks.toggle', $subtask));

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'subtask' => [
            'id' => $subtask->id,
            'is_completed' => true,
        ],
        'progress' => [
            'total' => 1,
            'completed' => 1,
            'percentage' => 100,
        ],
    ]);

    expect($subtask->fresh()->is_completed)->toBeTrue();

    // Uncheck kembali jadi belum selesai
    $uncheckResponse = $this->actingAs($this->user)->patchJson(route('subtasks.toggle', $subtask));
    $uncheckResponse->assertOk();
    expect($subtask->fresh()->is_completed)->toBeFalse();
});

test('user cannot toggle subtask belonging to another users task', function () {
    $otherSubtask = Subtask::factory()->create([
        'task_id' => $this->otherTask->id,
        'is_completed' => false,
    ]);

    $response = $this->actingAs($this->user)->patchJson(route('subtasks.toggle', $otherSubtask));

    $response->assertForbidden();
    expect($otherSubtask->fresh()->is_completed)->toBeFalse();
});

test('user can delete their own subtask', function () {
    $subtask = Subtask::factory()->create([
        'task_id' => $this->myTask->id,
    ]);

    $response = $this->actingAs($this->user)->deleteJson(route('subtasks.destroy', $subtask));

    $response->assertOk();
    $this->assertDatabaseMissing('subtasks', [
        'id' => $subtask->id,
    ]);
});

test('user cannot delete subtask belonging to another user', function () {
    $otherSubtask = Subtask::factory()->create([
        'task_id' => $this->otherTask->id,
    ]);

    $response = $this->actingAs($this->user)->deleteJson(route('subtasks.destroy', $otherSubtask));

    $response->assertForbidden();
    $this->assertDatabaseHas('subtasks', [
        'id' => $otherSubtask->id,
    ]);
});

test('task computes progress percentage and subtask counts accurately', function () {
    $task = Task::factory()->create(['user_id' => $this->user->id]);

    expect($task->subtasks_count)->toBe(0);
    expect($task->completed_subtasks_count)->toBe(0);
    expect($task->subtasks_progress_percentage)->toBe(0);

    Subtask::factory()->create(['task_id' => $task->id, 'is_completed' => true]);
    Subtask::factory()->create(['task_id' => $task->id, 'is_completed' => true]);
    Subtask::factory()->create(['task_id' => $task->id, 'is_completed' => false]);
    Subtask::factory()->create(['task_id' => $task->id, 'is_completed' => false]);

    $task->unsetRelation('subtasks');

    expect($task->subtasks_count)->toBe(4);
    expect($task->completed_subtasks_count)->toBe(2);
    expect($task->subtasks_progress_percentage)->toBe(50);
});
