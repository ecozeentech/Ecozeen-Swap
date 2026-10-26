<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageBulkActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function makeMessages(int $count = 3): array
    {
        return ContactMessage::factory()->count($count)->create(['status' => 'new'])->pluck('id')->all();
    }

    public function test_admin_can_bulk_mark_messages_as_read(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $ids = $this->makeMessages(3);

        $response = $this->actingAs($admin)->post(route('admin.contact-messages.bulk'), [
            'action' => 'mark-read',
            'ids' => $ids,
        ]);

        $response->assertRedirect(route('admin.contact-messages.index'));
        foreach ($ids as $id) {
            $this->assertDatabaseHas('contact_messages', ['id' => $id, 'status' => 'read']);
        }
    }

    public function test_admin_can_bulk_mark_messages_as_replied(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $ids = $this->makeMessages(2);

        $this->actingAs($admin)->post(route('admin.contact-messages.bulk'), [
            'action' => 'mark-replied',
            'ids' => $ids,
        ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('contact_messages', ['id' => $id, 'status' => 'replied']);
        }
    }

    public function test_admin_can_bulk_delete_messages(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $ids = $this->makeMessages(3);

        $this->actingAs($admin)->post(route('admin.contact-messages.bulk'), [
            'action' => 'delete',
            'ids' => $ids,
        ]);

        foreach ($ids as $id) {
            $this->assertDatabaseMissing('contact_messages', ['id' => $id]);
        }
    }

    public function test_bulk_delete_only_affects_selected_ids(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $toDelete = $this->makeMessages(2);
        $toKeep = $this->makeMessages(2);

        $this->actingAs($admin)->post(route('admin.contact-messages.bulk'), [
            'action' => 'delete',
            'ids' => $toDelete,
        ]);

        foreach ($toDelete as $id) {
            $this->assertDatabaseMissing('contact_messages', ['id' => $id]);
        }
        foreach ($toKeep as $id) {
            $this->assertDatabaseHas('contact_messages', ['id' => $id]);
        }
    }

    public function test_bulk_action_requires_at_least_one_id(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $response = $this->actingAs($admin)->post(route('admin.contact-messages.bulk'), [
            'action' => 'delete',
            'ids' => [],
        ]);

        $response->assertSessionHasErrors('ids');
    }

    public function test_non_admin_cannot_bulk_delete_messages(): void
    {
        $user = User::factory()->create();
        $ids = $this->makeMessages(1);

        $this->actingAs($user)->post(route('admin.contact-messages.bulk'), [
            'action' => 'delete',
            'ids' => $ids,
        ])->assertForbidden();

        $this->assertDatabaseHas('contact_messages', ['id' => $ids[0]]);
    }
}
