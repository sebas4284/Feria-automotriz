<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VehiculoFotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_vehiculo_with_photo_stores_it_on_the_public_disk(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->post('/vehiculos', [
            'placa' => 'FOT111',
            'marca' => 'M',
            'linea' => 'L',
            'modelo' => 2024,
            'estado' => 'Disponible',
            'foto' => UploadedFile::fake()->image('carro.jpg'),
        ])->assertRedirect(route('vehiculos.index'));

        $vehiculo = Vehiculo::where('placa', 'FOT111')->firstOrFail();
        $this->assertNotNull($vehiculo->foto);
        Storage::disk('public')->assertExists($vehiculo->foto);
    }

    public function test_updating_vehiculo_with_new_photo_deletes_the_old_one(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['rol' => 'admin']);
        $oldPath = UploadedFile::fake()->image('vieja.jpg')->store('vehiculos', 'public');
        $vehiculo = Vehiculo::create([
            'placa' => 'FOT222', 'marca' => 'M', 'linea' => 'L', 'modelo' => 2024,
            'estado' => 'Disponible', 'foto' => $oldPath,
        ]);

        $this->actingAs($admin)->put("/vehiculos/{$vehiculo->id}", [
            'placa' => 'FOT222',
            'marca' => 'M',
            'linea' => 'L',
            'modelo' => 2024,
            'estado' => 'Disponible',
            'foto' => UploadedFile::fake()->image('nueva.jpg'),
        ])->assertRedirect(route('vehiculos.show', $vehiculo));

        $vehiculo->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($vehiculo->foto);
    }

    public function test_updating_vehiculo_without_a_new_photo_keeps_the_existing_one(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['rol' => 'admin']);
        $path = UploadedFile::fake()->image('carro.jpg')->store('vehiculos', 'public');
        $vehiculo = Vehiculo::create([
            'placa' => 'FOT333', 'marca' => 'M', 'linea' => 'L', 'modelo' => 2024,
            'estado' => 'Disponible', 'foto' => $path,
        ]);

        $this->actingAs($admin)->put("/vehiculos/{$vehiculo->id}", [
            'placa' => 'FOT333',
            'marca' => 'M',
            'linea' => 'L',
            'modelo' => 2024,
            'estado' => 'Disponible',
        ])->assertRedirect(route('vehiculos.show', $vehiculo));

        $this->assertEquals($path, $vehiculo->fresh()->foto);
        Storage::disk('public')->assertExists($path);
    }

    public function test_deleting_vehiculo_removes_its_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['rol' => 'admin']);
        $path = UploadedFile::fake()->image('carro.jpg')->store('vehiculos', 'public');
        $vehiculo = Vehiculo::create([
            'placa' => 'FOT444', 'marca' => 'M', 'linea' => 'L', 'modelo' => 2024,
            'estado' => 'Disponible', 'foto' => $path,
        ]);

        $this->actingAs($admin)->delete("/vehiculos/{$vehiculo->id}")
            ->assertRedirect(route('vehiculos.index'));

        Storage::disk('public')->assertMissing($path);
    }
}
