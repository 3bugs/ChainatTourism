package th.ac.dusit.dbizcom.chainattourism;

import android.content.Intent;
import android.os.Bundle;

import com.google.gson.Gson;

import java.util.Locale;

import th.ac.dusit.dbizcom.chainattourism.etc.Utils;
import th.ac.dusit.dbizcom.chainattourism.fragment.PlaceListFragment;
import th.ac.dusit.dbizcom.chainattourism.model.Place;

import static th.ac.dusit.dbizcom.chainattourism.PlaceDetailsActivity.KEY_PLACE_JSON;

public class PlaceActivity extends BaseActivity implements
        PlaceListFragment.PlaceListFragmentListener {

    static final String KEY_PLACE_TYPE = "place_type";
    private static final String TAG_PLACE_LIST_FRAGMENT = "place_list_fragment";

    private Place.PlaceType mPlaceType;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_place);

        Intent intent = getIntent();
        mPlaceType = (Place.PlaceType) intent.getSerializableExtra(KEY_PLACE_TYPE);

        loadFragment(
                PlaceListFragment.newInstance(mPlaceType),
                TAG_PLACE_LIST_FRAGMENT,
                false,
                FragmentTransitionType.NONE
        );
    }

    @Override
    public void onClickPlace(Place place) {
        String msg = String.format(
                Locale.getDefault(),
                "%s\nอำเภอ: %s\nเบอร์โทร: %s\nพิกัด %.6f, %.6f\nType: %s",
                place.name, place.district, place.phone, place.latitude, place.longitude, place.placeType
        );
        Utils.showShortToast(this, msg);

        Intent intent = new Intent(PlaceActivity.this, PlaceDetailsActivity.class);
        intent.putExtra(KEY_PLACE_JSON, new Gson().toJson(place));
        startActivity(intent);
    }
}
