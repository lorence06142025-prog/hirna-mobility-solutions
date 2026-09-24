path = r'c:\xamppp\htdocs\TNVS\resources\views\vehicles\index.blade.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

old_block = """                                                             <option value="Sedan" {{ $vehicle->type === 'Sedan' ? 'selected' : '' }}>Sedan (Nerio Green)</option>
                                                             <option value="SUV" {{ $vehicle->type === 'SUV' ? 'selected' : '' }}>SUV (VF 8 / VF 9)</option>
                                                             <option value="Crossover" {{ $vehicle->type === 'Crossover' ? 'selected' : '' }}>Crossover (VF e34)</option>
                                                             <option value="Hatchback" {{ $vehicle->type === 'Hatchback' ? 'selected' : '' }}>Compact (VF 5)</option>"""

new_block = """                                                             <option value="Taxi Sedan" {{ $vehicle->type === 'Taxi Sedan' || $vehicle->type === 'Sedan' ? 'selected' : '' }}>Taxi Sedan (Toyota Vios / Accent / Almera)</option>
                                                             <option value="MPV / SUV" {{ $vehicle->type === 'MPV / SUV' || $vehicle->type === 'SUV' ? 'selected' : '' }}>MPV / SUV (Toyota Innova / SUV)</option>
                                                             <option value="Shuttle Van" {{ $vehicle->type === 'Shuttle Van' || $vehicle->type === 'Van' ? 'selected' : '' }}>Shuttle Van (Toyota HiAce)</option>
                                                             <option value="Electric Vehicle (EV)" {{ str_contains(strtolower($vehicle->type), 'ev') || str_contains(strtolower($vehicle->type), 'electric') ? 'selected' : '' }}>Electric Vehicle (EV - VinFast / Nerio Green)</option>
                                                             <option value="Hirna Traysikel" {{ str_contains(strtolower($vehicle->type), 'traysikel') ? 'selected' : '' }}>Hirna Traysikel (3-Wheeler Transport)</option>"""

content = content.replace(old_block, new_block)
content = content.replace("Battery Storage (kWh)", "Tank / Battery Capacity (Liters / kWh)")

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Modal options updated successfully!")
