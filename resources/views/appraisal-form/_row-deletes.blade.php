{{--
    The remove-row forms for the setup screen, one per part, mark and band.

    They live here, outside the form that saves the screen, because their
    buttons sit in table cells inside it - an HTML form cannot contain another.
    Each button reaches its form by id through the `form` attribute; see
    components/appraisal-row-delete.blade.php.
--}}
@foreach ($template->sections as $section)
    <form id="del-part-{{ $section->id }}" method="POST" class="js-confirm-delete"
          action="{{ route('appraisal-form.parts.destroy', $section->id) }}"
          data-confirm-title="Remove part"
          data-confirm="Remove &quot;{{ $section->title }}&quot;? Any categories under it move to the first remaining part."
          data-confirm-label="Remove">
        @csrf @method('DELETE')
    </form>
@endforeach

@foreach ($template->ratings as $rating)
    @continue(in_array($rating->value, $usedMarks, true))
    <form id="del-mark-{{ $rating->id }}" method="POST" class="js-confirm-delete"
          action="{{ route('appraisal-form.marks.destroy', $rating->id) }}"
          data-confirm-title="Remove mark"
          data-confirm="Remove &quot;{{ $rating->value }} - {{ $rating->label }}&quot; from the rating scale?"
          data-confirm-label="Remove">
        @csrf @method('DELETE')
    </form>
@endforeach

@foreach ($template->bands as $band)
    <form id="del-band-{{ $band->id }}" method="POST" class="js-confirm-delete"
          action="{{ route('appraisal-form.bands.destroy', $band->id) }}"
          data-confirm-title="Remove band"
          data-confirm="Remove the band &quot;{{ $band->label }}&quot;?"
          data-confirm-label="Remove">
        @csrf @method('DELETE')
    </form>
@endforeach
