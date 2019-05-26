package th.ac.dusit.dbizcom.chainattourism;

import android.os.Bundle;

import java.util.Locale;

import th.ac.dusit.dbizcom.chainattourism.etc.Utils;
import th.ac.dusit.dbizcom.chainattourism.fragment.PlaceListFragment;
import th.ac.dusit.dbizcom.chainattourism.model.Place;

public class PlaceActivity extends BaseActivity implements
        PlaceListFragment.PlaceListFragmentListener {

    private static final String TAG_PLACE_LIST_FRAGMENT = "place_list_fragment";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_place);

        loadFragment(
                new PlaceListFragment(),
                TAG_PLACE_LIST_FRAGMENT,
                false,
                FragmentTransitionType.NONE
        );
    }

    @Override
    public void onClickPlace(Place place) {
        String msg = String.format(
                Locale.getDefault(),
                "%s\nอำเภอ: %s\nเบอร์โทร: %s\nพิกัด %.6f, %.6f",
                place.name, place.district, place.phone, place.latitude, place.longitude
        );
        Utils.showShortToast(this, msg);
    }
}
