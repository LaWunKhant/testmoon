<h1>Houses</h1>
<ul>
    @foreach ($houses as $house)
        <li>
            {{ $house->name }} - {{ $house->address }}
            <a href="{{ route('houses.show', $house->id) }}">View Details</a>
            <a href="{{ route('houses.edit', $house->id) }}">Edit</a>
        </li>
    @endforeach
</ul>
<a href="{{ route('houses.create') }}">Create New House</a>